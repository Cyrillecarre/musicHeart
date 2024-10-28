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

        return $this->redirectToRoute('spotify_auth');
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
                // Ajoutez une valeur par défaut ou ignorez cette entrée
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

        $accessToken = $request->getSession()->get('spotify_access_token');
        $response = $this->httpClient->request('GET', "https://api.spotify.com/v1/tracks/$trackId", [
            'headers' => [
                'Authorization' => "Bearer $accessToken",
            ],
        ]);

        $data = $response->toArray();

        return [
            'name' => $data['name'],
            'image' => $data['album']['images'][0]['url'] ?? null,
            'url' => $data['external_urls']['spotify'] ?? $url,
        ];
    }

    #[Route('/submit-association', name: 'submit_association', methods: ['POST'])]
    public function submitAssociation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $associations = json_decode($request->getContent(), true)['associations'];
        $correctAssociations = $entityManager->getRepository(Participation::class)->findAll();
    
        $results = [];
        foreach ($associations as $musicId => $participantId) {
            $isCorrect = false;
            foreach ($correctAssociations as $participation) {
                if ($participation->getMusicUrl() === $musicId && $participation->getParticipant()->getId() == $participantId) {
                    $isCorrect = true;
                    break;
                }
            }
            $results[$musicId] = $isCorrect;
        }
    
        return new JsonResponse(['results' => $results]);
    }
}
