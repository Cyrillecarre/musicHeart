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
    public function index(): Response
    {
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
