<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     *  Affiche et traite le formulaire pour demander une réinitialisation du mot de passe.
     */
    #[Route('', name: 'app_forgot_password_request')]
    public function request(Request $request, MailerInterface $mailer, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $email */
            $email = $form->get('email')->getData();

            return $this->processSendingPasswordResetEmail(
                $email,
                $mailer,
                $translator
            );
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form,
        ]);
    }

    /**
     * Confirmation page after a user has requested a password reset.
     */
    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {

        $this->addFlash(
            'reset_password_info',
            'S\'il existe un compte correspondant à votre adresse email, vous recevrez un lien de réinitialisation. Ce lien expirera dans 1 heure. Si vous ne recevez pas d\'email, veuillez vérifier votre dossier spam.'
        );

        return $this->redirectToRoute('app_forgot_password_request');
    }

    /**
     * Valide et traite l'URL de réinitialisation sur laquelle l'utilisateur a cliqué dans son email.
     */
    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, string $token = null): Response
    {
        if ($token) {
            // On stocke le jeton en session et on le supprime de l'URL, pour éviter que l'URL ne soit
            // chargée dans un navigateur et ne divulgue potentiellement le jeton à du JavaScript tiers.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();

        if (null === $token) {
            throw $this->createNotFoundException('Aucun jeton de réinitialisation du mot de passe trouvé dans l\'URL ou dans la session.');
        }

        try {
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash(
                'reset_password_error',
                'Un problème est survenu lors de la validation de votre demande de réinitialisation de mot de passe - Le lien de réinitialisation du mot de passe n\'est pas valide. Veuillez réessayer de réinitialiser votre mot de passe.'
            );

            return $this->redirectToRoute('app_forgot_password_request');
        }

        // Le jeton est valide, on peut donc afficher le formulaire de réinitialisation du mot de passe.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Le mot de passe a été modifié, on supprime donc le jeton.
            $this->resetPasswordHelper->removeResetRequest($token);

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Encode(hash) le mot de passe en clair, et le définit.
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $this->entityManager->flush();

            // La session est nettoyée après que le mot de passe ait été changé.
            $this->cleanSessionAfterReset();

            $this->addFlash(
                'success',
                'Votre mot de passe a bien été réinitialisé.'
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form,
        ]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer, TranslatorInterface $translator): RedirectResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Ne révélez pas si un compte utilisateur a été trouvé ou non.
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {

            return $this->redirectToRoute('app_check_email');
        }

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@gmail.com', 'Admin Authentification'))
            ->to((string) $user->getEmail())
            ->subject('Votre demande de réinitialisation')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);
        $mailer->send($email);

        // Stock l'objet jeton en session pour le récupérer dans la route check-email.
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email');
    }
}
