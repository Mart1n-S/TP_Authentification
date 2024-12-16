<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthenticationAlertService
{
    private MailerInterface $mailer;
    private EntityManagerInterface $entityManager;

    public function __construct(MailerInterface $mailer, EntityManagerInterface $entityManager)
    {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
    }

    public function handleFailedLogin(SessionInterface $session, string $email): void
    {
        // Augmentez le compteur d'échecs
        $attempts = $session->get('login_attempts', 0) + 1;
        $session->set('login_attempts', $attempts);

        // Si l'utilisateur a atteint 5 échecs, envoyez un email
        if ($attempts >= 5) {
            $this->sendNotificationEmail($email);
            $session->remove('login_attempts'); // Réinitialiser le compteur
        }
    }

    private function sendNotificationEmail(string $email): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $email,
        ]);
        if (!$user) {
            return;
        }

        $emailMessage = (new TemplatedEmail())
            ->from(new Address('no-reply@gmail.com', 'Admin Authentification'))
            ->to($email)
            ->subject('Alerte de sécurité : tentatives de connexion échouées')
            ->htmlTemplate('emails/alert.html.twig');

        $this->mailer->send($emailMessage);
    }
}
