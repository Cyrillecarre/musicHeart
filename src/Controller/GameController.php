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
use App\Entity\Game;
use App\Form\GameType;
use Symfony\Component\Uid\Uuid;
use App\Entity\Admin;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $admin = $this->getUser();
        $patient = $entityManager->getRepository(Patient::class)->findBy(['admin' => $admin]);

        return $this->render('game/patient.html.twig', [
            'patientForm' => $form->createView(),
            'patients' => $patient,
        ]);
    }

    #[Route('/create-participant', name: 'create_participant')]
    public function createParticipant(Request $request, EntityManagerInterface $entityManager): Response
    {
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
            'participants' => $participants,
            'gameForm' => $gameForm->createView(),
            'games' => $games,

        ]);
    }

    #[Route('/paiement', name: 'app_paiement')]
    public function paiement(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): Response
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

        $participantToken = Uuid::v4();
        $participantLink = $urlGenerator->generate('add_participant', ['token' => $participantToken], UrlGeneratorInterface::ABSOLUTE_URL);

    
        return $this->render('game/paiement.html.twig', [
            'participantLink' => $participantLink,
            'game' => $game,
            'patient' => $patient,
        ]);
    }

}    