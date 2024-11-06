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

class GameController extends AbstractController
{

    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/envoyer-sms', name: 'send_sms', methods: ['POST'])]
    public function envoyerSms(Request $request, MailerInterface $mailer): JsonResponse
    {
        $numero = $request->request->get('numero');
        $message = $request->request->get('message');
        $apiKey = $_ENV['NUMVERIFY_API_KEY'];

        // Appel à l'API NumVerify
        $response = $this->httpClient->request('GET', "http://apilayer.net/api/validate?access_key=$apiKey&number=$numero&country_code=FR&format=1");

        $data = $response->toArray();

        if (!$data['valid']) {
            return new JsonResponse(['error' => 'Numéro invalide'], 400);
        }

        $operatorEmailDomains = [
            'Bouygues Telecom' => 'mms.bouyguestelecom.fr',
            'SFR' => 'sfr.fr',
            'Orange' => 'sms.orange.fr',
            'Free Mobile' => 'sms.free.fr'
        ];

        $operator = $data['carrier'] ?? '';
        $operatorEmailDomain = $operatorEmailDomains[$operator] ?? null;

        if (!$operatorEmailDomain) {
            return new JsonResponse(['error' => 'Opérateur non pris en charge'], 400);
        }

        $emailToSms = $numero . '@' . $operatorEmailDomain;

        $email = (new Email())
            ->from('votre_email@example.com')
            ->to($emailToSms)
            ->subject('')
            ->text($message);

        $mailer->send($email);

        return new JsonResponse(['success' => 'SMS envoyé avec succès']);
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
    public function createParticipant(Request $request, EntityManagerInterface $entityManager): Response
    {
        $participants = $entityManager->getRepository(Participant::class)->findAll();
        $participant = new Participant();
        $game = new Game();
        $gameForm = $this->createForm(GameType::class, $game);
        $gameForm->handleRequest($request);

        if ($gameForm->isSubmitted() && $gameForm->isValid()) {
            $admin = $this->getUser();
            $game->setAdmin($admin);
            $participant->setRoles(['ROLE_USER']);

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
        $patientLink = $urlGenerator->generate('patient_game_login', ['token' => $patientToken], UrlGeneratorInterface::ABSOLUTE_URL);

        $participantToken = Uuid::v4();
        $participantLink = $urlGenerator->generate('add_participant', ['token' => $participantToken,'adminId' => $admin->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->render('game/paiement.html.twig', [
            'patientLink' => $patientLink,
            'participantLink' => $participantLink,
            'game' => $game,
            'patient' => $patient,
            'admin' => $admin,
        ]);
    }
}    