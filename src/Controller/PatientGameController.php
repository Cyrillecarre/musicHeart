<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\Participation;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Participant;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class PatientGameController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/patient_game_login', name: 'patient_game_login')]
    public function login(Request $request): Response
    {
        $token = $request->query->get('token');
        if (!$token) {
            throw $this->createNotFoundException('Jeton invalide.');
        }
        
        return $this->redirectToRoute('spotify_auth_patient');
    }

    #[Route('/patient_game', name: 'patient_game_index')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $participations = $entityManager->getRepository(Participation::class)->findAll();
        $musicDetails = [];

        foreach ($participations as $participation) {
            $url = $participation->getMusicUrl();
            $musicData = $this->fetchSpotifyMusicDetails($url, $request);
    
            if (is_array($musicData) && isset($musicData['name'], $musicData['image'], $musicData['url'])) {
                $musicDetails[] = $musicData;
            } else {
                $musicDetails[] = [
                    'name' => 'Musique inconnue',
                    'image' => '/path/to/default/image.jpg',
                    'url' => $url
                ];
            }
        }

        return $this->render('patient_game/index.html.twig', [
            'musicDetails' => $musicDetails,
            'participations' => $participations,
        ]);
    }

    private function fetchSpotifyMusicDetails(string $url, Request $request): array
    {
        preg_match('/track\/([a-zA-Z0-9]+)/', $url, $matches);
        $trackId = $matches[1] ?? null;

        if (!$trackId) {
            return ['name' => 'Unknown', 'image' => null, 'url' => $url];
        }

        $accessToken = $request->getSession()->get('spotify_access_token_patient');
        $response = $this->httpClient->request('GET', "https://api.spotify.com/v1/tracks/$trackId", [
            'headers' => [
                'Authorization' => "Bearer $accessToken",
            ],
        ]);

        // Vérifiez si le token est expiré
        if ($response->getStatusCode() === 401) {
            // Actualisez le token en utilisant le refresh_token
            if ($this->refreshSpotifyAccessToken($request)) {
                // Réessayez avec le nouveau token
                $accessToken = $request->getSession()->get('spotify_access_token_patient');
                $response = $this->httpClient->request('GET', "https://api.spotify.com/v1/tracks/$trackId", [
                    'headers' => [
                        'Authorization' => "Bearer $accessToken",
                    ],
                ]);
            } else {
                return ['name' => 'Token expired', 'image' => null, 'url' => $url];
            }
        }

        $data = $response->toArray();
        return [
            'name' => $data['name'],
            'image' => $data['album']['images'][0]['url'] ?? null,
            'url' => $data['external_urls']['spotify'] ?? $url,
        ];
    }

    private function refreshSpotifyAccessToken(Request $request): bool
    {
        $refreshToken = $request->getSession()->get('spotify_refresh_token_patient');
        if (!$refreshToken) {
            return false;
        }

        $clientId = $_ENV['SPOTIFY_CLIENT_ID'];
        $clientSecret = $_ENV['SPOTIFY_CLIENT_SECRET'];
        $url = 'https://accounts.spotify.com/api/token';
        $headers = [
            'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret),
            'Content-Type: application/x-www-form-urlencoded',
        ];
        $postFields = http_build_query([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);

        $response = $this->httpPostPatient($url, $headers, $postFields);
        $data = json_decode($response, true);

        if (isset($data['access_token'])) {
            $session = $request->getSession();
            $session->set('spotify_access_token_patient', $data['access_token']);
            return true;
        }

        return false;
    }


    #[Route('/submit-association', name: 'submit_association', methods: ['POST'])]
    public function submitAssociation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $submittedAssociations = json_decode($request->getContent(), true)['associations'] ?? [];
        $results = [];
        $correctAssociations = [];
    
        $participations = $entityManager->getRepository(Participation::class)->findAll();
    
        // Comparaison de chaque association soumise avec les participations
        foreach ($submittedAssociations as $musicUrl => $participantData) {
            $participantId = $participantData['participantId'];
            $isCorrect = false;
    
            foreach ($participations as $participation) {
                if ($participation->getMusicUrl() === $musicUrl && $participation->getParticipant()->getId() == $participantId) {
                    $isCorrect = true;
                    $correctAssociations[] = [
                        'musicName' => $participation->getMusicUrl(),
                        'supportText' => $participation->getSupportText(),
                    ];
                    break;
                }
            }
            $results[$musicUrl] = $isCorrect;
        }
    
        return new JsonResponse(['results' => $results, 'correctAssociations' => $correctAssociations]);
    }

    #[Route('/patient_game/result', name: 'patient_game_result')]
    public function resultPage(EntityManagerInterface $entityManager): Response
    {
        $participations = $entityManager->getRepository(Participation::class)->findAll();
        
        return $this->render('patient_game/result.html.twig', [
            'participations' => $participations,
        ]);
    }
    
    #[Route('/spotify-login-patient', name: 'spotify_login_patient')]
    public function spotifyLoginPatient(Request $request): Response
    {
        $sessionId = uniqid();
        $clientId = $_ENV['SPOTIFY_CLIENT_ID'];
        $redirectUri = $_ENV['SPOTIFY_REDIRECT_URI_PATIENT'];
        $scopes = 'user-read-private user-read-email playlist-read-private';

        $authorizationUrl = "https://accounts.spotify.com/authorize?response_type=code&client_id=$clientId&redirect_uri=$redirectUri&scope=$scopes&state=$sessionId";

        return $this->redirect($authorizationUrl);
    }

    #[Route('/callback-patient', name: 'spotify_callback_patient')]
    public function spotifyCallbackPatient(Request $request): Response
    {
        $code = $request->query->get('code');

        if (!$code) {
            return $this->redirectToRoute('spotify_login_patient');
        }

        $tokenResponse = $this->getSpotifyAccessTokenPatient($code);
        if (!isset($tokenResponse['access_token'])) {
            $this->addFlash('error', 'Erreur lors de la récupération du jeton d\'accès Spotify.');
            return $this->redirectToRoute('spotify_login_patient');
        }

        $session = $request->getSession();
        $session->set('spotify_access_token_patient', $tokenResponse['access_token']);
        if (isset($tokenResponse['refresh_token'])) {
            $session->set('spotify_refresh_token_patient', $tokenResponse['refresh_token']);
        }

        return $this->redirectToRoute('patient_game_index');
    }

    private function getSpotifyAccessTokenPatient(string $code): array
    {
        $clientId = $_ENV['SPOTIFY_CLIENT_ID'];
        $clientSecret = $_ENV['SPOTIFY_CLIENT_SECRET'];
        $redirectUri = $_ENV['SPOTIFY_REDIRECT_URI_PATIENT'];

        $url = 'https://accounts.spotify.com/api/token';
        $headers = [
            'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret),
            'Content-Type: application/x-www-form-urlencoded',
        ];

        $postFields = http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ]);

        $response = $this->httpPostPatient($url, $headers, $postFields);
        return json_decode($response, true);
    }

    private function httpPostPatient(string $url, array $headers, string $postFields): string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    #[Route('/spotify-auth-patient', name: 'spotify_auth_patient')]
    public function authSpotifyPatient(): Response
    {
        $clientId = $_ENV['SPOTIFY_CLIENT_ID'];
        $redirectUri = $_ENV['SPOTIFY_REDIRECT_URI_PATIENT'];

        $scopes = 'user-read-private user-read-email playlist-read-private';

        $authUrl = 'https://accounts.spotify.com/authorize?response_type=code&client_id=' . $clientId .
           '&scope=' . urlencode($scopes) . '&redirect_uri=' . urlencode($redirectUri);

        return $this->redirect($authUrl);
    }

    #[Route('/send-results/{participantId}', name: 'send_results')]
    public function sendResults(int $participantId, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        // Récupérer le participant
        $participant = $entityManager->getRepository(Participant::class)->find($participantId);
        if (!$participant) {
            throw $this->createNotFoundException('Participant non trouvé.');
        }

        // Récupérer les participations et calculer les résultats
        $participations = $entityManager->getRepository(Participation::class)->findAll();
        $correctCount = 0;
        $totalAssociations = count($participations);
        $resultDetails = '';

        foreach ($participations as $participation) {
            $correctAssociation = $participation->getParticipant()->getId() === $participant->getId();
            if ($correctAssociation) {
                $correctCount++;
            }

            // Construction de la chaîne de résultat pour chaque musique
            $resultDetails .= '<p><strong>Musique :</strong> ' . htmlspecialchars($participation->getMusicUrl()) . '</p>';
            $resultDetails .= '<p><strong>Participant associé :</strong> ' . htmlspecialchars($participation->getParticipant()->getName()) . '</p>';
            $resultDetails .= '<p><strong>Texte de soutien :</strong> ' . htmlspecialchars($participation->getSupportText()) . '</p>';
            $resultDetails .= '<p><strong>Résultat :</strong> ' . ($correctAssociation ? 'Correct' : 'Faux') . '</p>';

            if (!$correctAssociation) {
                $correctParticipant = $participation->getParticipant()->getName();
                $resultDetails .= '<p><strong>Bonne association :</strong> ' . htmlspecialchars($correctParticipant) . '</p>';
            }

            $resultDetails .= '<hr>';
        }

        $email = (new Email())
            ->from('votre-email@domaine.com')
            ->to($participant->getEmail())
            ->subject('Résultats du jeu d\'association')
            ->html("
                <h2>Résultats du jeu d'association</h2>
                <p><strong>Total de bonnes réponses :</strong> $correctCount / $totalAssociations</p>
                <div>$resultDetails</div>
            ");

        // Envoi de l'email
        $mailer->send($email);

        return new Response('Les résultats ont été envoyés par email.');
    }
}

