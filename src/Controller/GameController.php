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
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\AccessToken;

class GameController extends AbstractController
{

    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

   
#[Route('/envoyer-email', name: 'envoyer_email', methods: ['POST'])]
public function envoyerEmail(Request $request, MailerInterface $mailer): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $adresseEmail = $data['adresseEmail'] ?? null;
    $message = $data['message'] ?? null;

    if (!$adresseEmail || !$message) {
        return new JsonResponse(['error' => 'Adresse email ou message manquant'], 400);
    }

    try {
        $email = (new Email())
            ->from('contact@ecfsymfony.online')
            ->to($adresseEmail)
            ->subject('Invitation à participer à Music Heart')
            ->text($message);

        $mailer->send($email);

        return new JsonResponse(['success' => 'Email envoyé avec succès']);
    } catch (\Exception $e) {
        return new JsonResponse(['error' => 'Erreur lors de l\'envoi de l\'email : ' . $e->getMessage()], 500);
    }
}

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
    public function createParticipant(Request $request, EntityManagerInterface $entityManager, int $adminId = null): Response
    {
        $admin = $this->getUser();
            if (!$admin) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }
        $participants = $entityManager->getRepository(Participant::class)->findAll();
        $activeGame = $entityManager->getRepository(Game::class)->findOneBy(['admin' => $admin]);
        $participant = new Participant();
        $game = $activeGame ?? new Game();
        $gameForm = $this->createForm(GameType::class, $game);
        $gameForm->handleRequest($request);

        if ($gameForm->isSubmitted() && $gameForm->isValid()) {
            $admin = $this->getUser();
            $game->setAdmin($admin);
            $participant->setRoles(['ROLE_USER']);

            $entityManager->persist($game);
            $entityManager->flush();

            return $this->redirectToRoute('create_participant', ['adminId' => $adminId]);
        }

        return $this->render('game/participant.html.twig', [
            'participants' => $participants,
            'gameForm' => $gameForm->createView(),
            'games' => $activeGame ? [$activeGame] : [],
            'admin' => $admin,
        ]);
    }

    #[Route('/edit-game/{id}', name: 'edit_game')]
    public function editGame(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $games = $entityManager->getRepository(Game::class)->find($id);

        if (!$games) {
            throw $this->createNotFoundException('Partie non trouvée');
        }

        $gameForm = $this->createForm(GameType::class, $games);
        $gameForm->handleRequest($request);

        if ($gameForm->isSubmitted() && $gameForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('create_participant');
        }

        return $this->render('game/edit_game.html.twig', [
            'gameForm' => $gameForm->createView(),
            'game' => $games,
        ]);
    }

    
    #[Route('/paiement', name: 'app_paiement')]
    public function paiement(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): Response
    {
        /** @var Admin $admin */
        $admin = $this->getUser();
        $patients = $admin->getPatients();
        $admin = $entityManager->getRepository(Admin::class)->findOneBy(['id' => $admin->getId()]);
    
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
    
        $patientToken = Uuid::v4();
        $accessTokenPatient = new AccessToken();
        $accessTokenPatient->setToken($patientToken);
        $accessTokenPatient->setAdmin($admin);
        $accessTokenPatient->setExpirationDate($game->getResultDate()); 
        $entityManager->persist($accessTokenPatient);
    
        $participantToken = Uuid::v4();
        $accessTokenParticipant = new AccessToken();
        $accessTokenParticipant->setToken($participantToken);
        $accessTokenParticipant->setAdmin($admin);
        $accessTokenParticipant->setExpirationDate($game->getEndDate());
        $entityManager->persist($accessTokenParticipant);
 
        $entityManager->flush();
    
        // Génération des liens
        $patientLink = $urlGenerator->generate('patient_game_login', ['token' => $patientToken], UrlGeneratorInterface::ABSOLUTE_URL);
        $participantLink = $urlGenerator->generate('add_participant', ['token' => $participantToken, 'adminId' => $admin->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
    
        // Rendu de la vue avec les informations nécessaires
        return $this->render('game/paiement.html.twig', [
            'patientLink' => $patientLink,
            'participantLink' => $participantLink,
            'game' => $game,
            'patient' => $patient,
            'admin' => $admin,
        ]);
    }   
}    