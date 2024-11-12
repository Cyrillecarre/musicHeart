<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Game;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $dateLimit = new \DateTime('-24 hours');

        $expiredGames = $entityManager->getRepository(Game::class)
            ->createQueryBuilder('g')
            ->where('g.result_date <= :dateLimit')
            ->setParameter('dateLimit', $dateLimit)
            ->getQuery()
            ->getResult();

        foreach ($expiredGames as $game) {

            foreach ($game->getParticipants() as $participant) {
                foreach ($participant->getParticipations() as $participation) {
                    $entityManager->remove($participation);
                }
                $entityManager->remove($participant);
            }

            foreach ($game->getAdmin()->getPatients() as $patient) {
                $entityManager->remove($patient);
            }

            foreach ($game->getAdmin()->getAccessTokens() as $accessToken) {
                $entityManager->remove($accessToken);
            }

            foreach ($game->getAdmin()->getSpotifySessions() as $spotifySession) {
                $entityManager->remove($spotifySession);
            }

            foreach ($game->getGuesses() as $guess) {
                $entityManager->remove($guess);
            }

            $entityManager->remove($game);
        }

        $entityManager->flush();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/auth', name: 'app_auth_page')]
    public function authPage(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($user) {
            $game = $entityManager->getRepository(Game::class)->findOneBy(['admin' => $user]);

            return $this->render('home/auth.html.twig', [
                'game' => $game,
            ]);
        }

        return $this->redirectToRoute('app_login');
    }
}
