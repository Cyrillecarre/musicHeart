<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\AppAuthenticator;
use App\Entity\Admin;
use App\Form\RegisterType;
use App\Entity\Game;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager,UserAuthenticatorInterface $userAuthenticator,AppAuthenticator $authenticator): Response {
        $user = new Admin();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            if ($plainPassword !== $confirmPassword) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');

                return $this->render('register/index.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            $existingAdmin = $entityManager->getRepository(Admin::class)->findOneBy(['email' => $user->getEmail()]);
            if ($existingAdmin) {
                $activeGame = $entityManager->getRepository(Game::class)->findOneBy(['admin' => $existingAdmin]);

                if ($activeGame) {
                    return $this->render('register/index.html.twig', [
                        'registrationForm' => $form->createView(),
                        'activeGame' => $activeGame,
                    ]);
                }
            }

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('register/index.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
