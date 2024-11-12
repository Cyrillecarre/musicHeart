<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Participation;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Participant;
use App\Entity\Game;

class StartGameController extends AbstractController
{
    #[Route('/start-game/{participantId}', name: 'app_start_game')]
    public function startGame(int $participantId, EntityManagerInterface $entityManager, Request $request): Response
    {
        $participant = $entityManager->getRepository(Participant::class)->find($participantId);
    
        if (!$participant) {
            throw $this->createNotFoundException('Participant non trouvé.');
        }

        $session = $request->getSession();
        $session->set('participant_id', $participantId);

        return $this->redirectToRoute('spotify_login', ['participantId' => $participantId]);
    }

    #[Route('/choose-music/{participantId}', name: 'choose_music')]
    public function chooseMusic(Request $request, int $participantId, EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        $accessToken = $session->get('spotify_access_token');
        $refreshToken = $session->get('spotify_refresh_token');
    
        if (!$accessToken) {
            return $this->redirectToRoute('spotify_login');
        }

        $participant = $entityManager->getRepository(Participant::class)->find($participantId);
    
        if (!$participant) {
            throw $this->createNotFoundException('Participant non trouvé.');
        }
    
        $query = $request->query->get('query', '');
        $tracks = [];
    
        try {
            if ($query) {
                $tracks = $this->searchSpotifyTracks($query, $accessToken);
            }
        } catch (\Exception $e) {
            if ($e->getMessage() === 'The access token expired' && $refreshToken) {
                $newTokenResponse = $this->refreshSpotifyAccessToken($refreshToken);
    
                if (isset($newTokenResponse['access_token'])) {
                    $session->set('spotify_access_token', $newTokenResponse['access_token']);
    
                    if ($query) {
                        $tracks = $this->searchSpotifyTracks($query, $newTokenResponse['access_token']);
                    }
                } else {
                    $this->addFlash('error', 'Erreur lors du rafraîchissement du jeton. Veuillez vous reconnecter.');
                    return $this->redirectToRoute('spotify_login');
                }
            }
        }
    
        return $this->render('start_game/choose_music.html.twig', [
            'tracks' => $tracks,
            'query' => $query,
            'participant' => $participant,
        ]);
    }
    
    
    private function refreshSpotifyAccessToken(string $refreshToken): array
    {
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

    private function searchSpotifyTracks(string $query, string $accessToken): array
    {
        $url = 'https://api.spotify.com/v1/search?type=track&q=' . urlencode($query);
        $headers = [
            'Authorization: Bearer ' . $accessToken,
        ];

        $response = $this->httpGet($url, $headers);
        $data = json_decode($response, true);

        if (isset($data['tracks']['items'])) {
            return $data['tracks']['items'];
        }

        $this->addFlash('error', 'Aucun résultat trouvé.');
        return [];
    }

    private function httpGet(string $url, array $headers): string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
    
    #[Route('/submit-music/{trackId}/{participantId}', name: 'submit_music')]
    public function submitMusic(string $trackId, int $participantId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $participant = $entityManager->getRepository(Participant::class)->find($participantId);

        if (!$participant) {
            throw $this->createNotFoundException('Participant non trouvé.');
        }

        $game = $entityManager->getRepository(Game::class)->findOneBy(['admin' => $participant->getAdmin()]);
        
        if (!$game) {
            throw $this->createNotFoundException('Aucun jeu trouvé pour ce participant.');
        }

        $participation = $entityManager->getRepository(Participation::class)->findOneBy([
            'participant' => $participant,
            'game' => $game
        ]);

        if (!$participation) {
            $participation = new Participation();
            $participation->setGame($game);
            $participation->setParticipant($participant);
        }

        if ($request->isMethod('POST')) {
            $supportText = $request->request->get('support_text');
            $participation->setMusicUrl('https://open.spotify.com/track/' . $trackId);
            $participation->setSupportText($supportText);

            $entityManager->persist($participation);
            $entityManager->flush();

            return $this->redirectToRoute('thank_you');
        }

        return $this->render('start_game/support_text.html.twig', [
            'trackId' => $trackId,
            'participant' => $participant,
        ]);
    } 
    #[Route('/thank-you', name: 'thank_you')]
    public function thankYou(): Response
    {
        return $this->render('start_game/thank_you.html.twig');
    }
}

