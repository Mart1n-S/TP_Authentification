<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class CheckVerifiedUserSubscriber implements EventSubscriberInterface
{
    public function onCheckPassport(CheckPassportEvent $event)
    {
        // On vérifie que le passport est bien une instance de Passport
        $passport = $event->getPassport();
        if (!$passport instanceof Passport) {
            throw new \Exception('Unexpected passport type');
        }

        // On vérifie que l'utilisateur est bien une instance de User
        $user = $passport->getUser();
        if (!$user instanceof User) {
            throw new \Exception('Unexpected user type');
        }

        // On vérifie que l'utilisateur est bien vérifié
        if (!$user->IsVerified()) {
            throw new CustomUserMessageAuthenticationException(
                'Veuillez vérifier votre compte avant de vous connecter.'
            );
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            CheckPassportEvent::class => ['onCheckPassport', -10],
        ];
    }
}
