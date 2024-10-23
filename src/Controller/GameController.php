<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Patient;
use App\Form\PatientType;
use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Entity\Game;
use App\Form\GameType;
use Symfony\Component\Uid\Uuid;
use App\Entity\Admin;

class GameController extends AbstractController
{
    #[Route('/game', name: 'app_game')]
    public function index(): Response
    {
        return $this->render('game/index.html.twig', [
            'controller_name' => 'GameController',
        ]);
    }

    #[Route('/create-patient', name: 'create_patient')]
    public function createPatient(Request $request, EntityManagerInterface $entityManager): Response
    {
        $patient = new Patient();

        $form = $this->createForm(PatientType::class, $patient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $admin = $this->getUser();
            $patient->setAdmin($admin);

            $entityManager->persist($patient);
            $entityManager->flush();

            return $this->redirectToRoute('create_patient');
        }
        $patient = $entityManager->getRepository(Patient::class)->findAll();

        return $this->render('game/patient.html.twig', [
            'patientForm' => $form->createView(),
            'patients' => $patient,
        ]);
    }

    #[Route('/create-participant', name: 'create_participant')]
    public function createParticipant(Request $request, EntityManagerInterface $entityManager): Response
    {
        $participant = new Participant();
        $participantForm = $this->createForm(ParticipantType::class, $participant);
        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            $admin = $this->getUser();
            $participant->setAdmin($admin);

            $entityManager->persist($participant);
            $entityManager->flush();

            return $this->redirectToRoute('create_participant');
        }

        $participants = $entityManager->getRepository(Participant::class)->findAll();

        $game = new Game();
        $gameForm = $this->createForm(GameType::class, $game);
        $gameForm->handleRequest($request);

        if ($gameForm->isSubmitted() && $gameForm->isValid()) {
            $admin = $this->getUser();
            $game->setAdmin($admin);

            $entityManager->persist($game);
            $entityManager->flush();

            return $this->redirectToRoute('create_participant');
        }

        $games = $entityManager->getRepository(Game::class)->findAll();

        return $this->render('game/participant.html.twig', [
            'participantForm' => $participantForm->createView(),
            'participants' => $participants,
            'gameForm' => $gameForm->createView(),
            'games' => $games,

        ]);
    }

    #[Route('/paiement', name: 'app_paiement')]
    public function paiement(EntityManagerInterface $entityManager): Response
    {
        /** @var Admin $admin */
        $admin = $this->getUser();

        $patients = $admin->getPatients();

        if ($patients->isEmpty()) {
            throw $this->createNotFoundException('Aucun patient trouvé.');
        }

        $patient = $patients->first();

        if (!$patient || !$patient->getName()) {
            throw $this->createNotFoundException('Patient sans nom trouvé.');
        }

        $game = $entityManager->getRepository(Game::class)->findOneBy(['admin' => $admin]);

        if (!$game) {
            throw $this->createNotFoundException('Aucun jeu trouvé.');
        }
    
        $token = Uuid::v4();
        $privateLink = 'https://ton-jeu.com/participation?token=' . $token;
    
        return $this->render('game/paiement.html.twig', [
            'privateLink' => $privateLink,
            'patient' => $patient,
            'game' => $game,
        ]);
    }
}    