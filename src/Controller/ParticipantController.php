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

class ParticipantController extends AbstractController
{
    #[Route('/participant', name: 'app_participant')]
    public function index(): Response
    {
        return $this->render('participant/index.html.twig', [
            'controller_name' => 'ParticipantController',

        ]);
    }

    #[Route('/add_participant/{adminId}', name: 'add_participant')]
    public function createParticipant(int $adminId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $admin = $entityManager->getRepository(Admin::class)->find($adminId);
    
        if (!$admin) {
            throw $this->createNotFoundException('Admin non trouvé.');
        }
    
        $participant = new Participant();
        $participantForm = $this->createForm(ParticipantType::class, $participant);
        $participantForm->handleRequest($request);
    
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
