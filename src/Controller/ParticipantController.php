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
    
        // Vérifier le token d'accès
        $accessToken = $entityManager->getRepository(AccessToken::class)->findOneBy(['token' => $token]);
        if (!$accessToken) {
            return $this->render('participant/tokenInvalide.html.twig', [
                'message' => 'Token invalide.',
            ]);
        }
    
        // Vérifier la validité du token
        $currentDate = new \DateTime();
        if ($currentDate > $accessToken->getExpirationDate()) {
            return $this->render('participant/tokenInvalide.html.twig', [
                'message' => 'Le token a expiré.',
            ]);
        }
    
        // Création du formulaire de participant
        $participant = new Participant();
        $participantForm = $this->createForm(ParticipantType::class, $participant);
        $participantForm->handleRequest($request);
    
        // Traitement du formulaire
        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
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
}
