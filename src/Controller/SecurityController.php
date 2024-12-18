<?php

namespace App\Controller;

use App\Service\AuthenticationAlertService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request, AuthenticationAlertService $authenticationAlertService): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_profil');
        }

        // Récupérer l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        if ($error) {
            $email = $authenticationUtils->getLastUsername();
            $session = $request->getSession();

            // Utiliser le service pour gérer les échecs de connexion
            $authenticationAlertService->handleFailedLogin($session, $email);
        }
        // Récupérer le dernier email saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        $siteKey = $_ENV['RECAPTCHA3_KEY'];

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error, 'siteKey' => $siteKey]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
