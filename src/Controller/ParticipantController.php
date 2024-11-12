<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Entity\Admin;
use App\Entity\AccessToken;
use App\Entity\Participation;


class ParticipantController extends AbstractController
{
    #[Route('/participant', name: 'app_participant')]
    public function index(): Response
    {
        return $this->render('participant/index.html.twig', [
            'controller_name' => 'ParticipantController',

        ]);
    }

    
    #[Route('/add_participant/{adminId}/{token}', name: 'add_participant')]
    public function createParticipant(string $token, int $adminId, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérifier l'existence de l'administrateur
        $admin = $entityManager->getRepository(Admin::class)->find($adminId);
        if (!$admin) {
            throw $this->createNotFoundException('Admin non trouvé.');
        }

        $accessToken = $entityManager->getRepository(AccessToken::class)->findOneBy(['token' => $token]);
        if (!$accessToken) {
            return $this->render('participant/tokenInvalide.html.twig', [
                'message' => 'Token invalide.',
            ]);
        }

        $currentDate = new \DateTime();
        if ($currentDate > $accessToken->getExpirationDate()) {
            return $this->render('participant/tokenInvalide.html.twig', [
                'message' => 'Le token a expiré.',
            ]);
        }

        $participant = new Participant();
        $participantForm = $this->createForm(ParticipantType::class, $participant);
        $participantForm->handleRequest($request);


        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            // Vérifier si un participant avec la même adresse e-mail existe déjà
            $existingParticipant = $entityManager->getRepository(Participant::class)->findOneBy(['email' => $participant->getEmail()]);
            
            if ($existingParticipant) {
                // Redirigez vers la page de mise à jour si le participant existe déjà
                return $this->redirectToRoute('update_participation', ['participantId' => $existingParticipant->getId()]);
            }
    
            // Enregistrer le nouveau participant
            $participant->setAdmin($admin);
            $participant->setRoles(['ROLE_USER']);
        
            $entityManager->persist($participant);
            $entityManager->flush();
        
            return $this->redirectToRoute('show_participant', ['id' => $participant->getId()]);
        }
    
        return $this->render('participant/index.html.twig', [
            'participantForm' => $participantForm->createView(),
        ]);
    }

    #[Route('/participant/{id}', name: 'show_participant')]
    public function showParticipant(int $id, EntityManagerInterface $entityManager): Response
    {
        $participant = $entityManager->getRepository(Participant::class)->find($id);

        if (!$participant) {
            throw $this->createNotFoundException('Participant non trouvé.');
        }

        return $this->render('participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/update_participation/{participantId}', name: 'update_participation', methods: ['GET', 'POST'])]
    public function updateParticipation(int $participantId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $participation = $entityManager->getRepository(Participation::class)->findOneBy(['participant' => $participantId]);
        $participant = $entityManager->getRepository(Participant::class)->find($participantId);
        
        if (!$participation) {
            throw $this->createNotFoundException('Participation non trouvée.');
        }

        if ($request->isMethod('POST')) {
            $musicUrl = $request->request->get('musicUrl');
            $supportText = $request->request->get('supportText');

            $participation->setMusicUrl($musicUrl);
            $participation->setSupportText($supportText);

            $entityManager->flush();

            return $this->redirectToRoute('show_participant', ['id' => $participantId]);
        }

        return $this->render('participant/update.html.twig', [
            'participation' => $participation,
            'participant' => $participant,
        ]);
    }
}
