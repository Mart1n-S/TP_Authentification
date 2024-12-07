<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier) {}

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_accueil');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@gmail.com', 'Admin Authentification'))
                    ->to($user->getEmail())
                    ->subject('Merci de confirmer votre email')
                    ->htmlTemplate('emails/confirmation_email.html.twig')
            );

            $this->addFlash(
                'success',
                'Votre compte a bien été créé. Merci de confirmer votre email.'
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        // On récupère l'utilisateur avec l'id passé en paramètre et qui n'a pas encore confirmé son email
        $user = $userRepository->findOneBy(['id' => $request->query->get('id'), 'isVerified' => false]);
        // Si l'utilisateur n'existe pas ou que son email a déjà été confirmé
        if (!$user) {
            $this->addFlash(
                'danger',
                'L\'utilisateur n\'a pas été trouvé ou son email a déjà été confirmé.'
            );

            return $this->redirectToRoute('app_login');
        }

        // On confirme l'email de l'utilisateur
        $response = $this->emailVerifier->handleEmailConfirmation($request, $user);

        // Si on à pas de réponse, c'est que l'email a bien été confirmé
        if ($response === null) {
            $this->addFlash(
                'success',
                'Votre email a bien été confirmé. Vous pouvez maintenant vous connecter.'
            );

            return $this->redirectToRoute('app_login');
        } else {
            $this->addFlash(
                'danger',
                $response
            );

            return $this->redirectToRoute('app_login');
        }
    }
}
