<?php
// SpotifyController.php
// SpotifyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\SpotifySession;
use Doctrine\ORM\EntityManagerInterface;

class SpotifyController extends AbstractController
{
    #[Route('/spotify-login/{participantId}', name: 'spotify_login')]
    public function spotifyLogin(Request $request, int $participantId, EntityManagerInterface $entityManager): Response
    {

        $request->getSession()->set('participant_id', $participantId);

        $sessionId = uniqid('spotify_', true);
    
        $spotifySession = new SpotifySession();
        $spotifySession->setSessionId($sessionId);
        $spotifySession->setParticipantId($participantId);
        $entityManager->persist($spotifySession);
        $entityManager->flush();
    
        $clientId = $_ENV['SPOTIFY_CLIENT_ID'];
        $redirectUri = $_ENV['SPOTIFY_REDIRECT_URI'];
        $scopes = 'user-read-private user-read-email playlist-read-private';
    
        $authorizationUrl = "https://accounts.spotify.com/authorize?response_type=code&client_id=$clientId&redirect_uri=$redirectUri&scope=$scopes&state=$sessionId";
    
        return $this->redirect($authorizationUrl);
    }
    
    #[Route('/callback', name: 'spotify_callback')]
    public function spotifyCallback(Request $request, EntityManagerInterface $entityManager): Response
    {
        $code = $request->query->get('code');
        $sessionId = $request->query->get('state');

        if (!$code) {
            // Redirige vers l'authentification Spotify appropriée en cas d'échec
            return $this->redirectToRoute($sessionId ? 'spotify_login' : 'spotify_auth');
        }

        $tokenResponse = $this->getSpotifyAccessToken($code);
        if (!isset($tokenResponse['access_token'])) {
            $this->addFlash('error', 'Erreur lors de la récupération du jeton d\'accès Spotify.');
            return $this->redirectToRoute($sessionId ? 'spotify_login' : 'spotify_auth');
        }

        // Stocker le token dans la session
        $session = $request->getSession();
        $session->set('spotify_access_token', $tokenResponse['access_token']);
        if (isset($tokenResponse['refresh_token'])) {
            $session->set('spotify_refresh_token', $tokenResponse['refresh_token']);
        }

        // Vérifie si l'utilisateur est un administrateur (patient) ou un participant
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('patient_game_index');
        }

        // Sinon, redirige vers `choose_music` avec `participantId`
        $participantId = $session->get('participant_id');
        return $this->redirectToRoute('choose_music', ['participantId' => $participantId]);
    }
    
    private function getSpotifyAccessToken(string $code): array
    {
        $clientId = $_ENV['SPOTIFY_CLIENT_ID'];
        $clientSecret = $_ENV['SPOTIFY_CLIENT_SECRET'];
        $redirectUri = $_ENV['SPOTIFY_REDIRECT_URI'];
    
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
    
        $response = $this->httpPost($url, $headers, $postFields);
        return json_decode($response, true);
    }

    private function httpPost(string $url, array $headers, string $postFields): string
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


    #[Route('/spotify-auth', name: 'spotify_auth')]
    public function authSpotify(): Response
    {
        $clientId = $_ENV['SPOTIFY_CLIENT_ID'];
        $redirectUri = $_ENV['SPOTIFY_REDIRECT_URI'];

        $scopes = 'user-read-private user-read-email playlist-read-private';

        $authUrl = 'https://accounts.spotify.com/authorize?response_type=code&client_id=' . $clientId .
           '&scope=' . urlencode($scopes) . '&redirect_uri=' . urlencode($redirectUri);
    
        return $this->redirect($authUrl);
    }  
}



