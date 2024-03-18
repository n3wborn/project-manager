<?php

namespace App\Security;

use App\Entity\User;
use App\Helper\ApiMessages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    final public function checkAccessFromController(UserInterface $user, Request $request): void
    {
        try {
            $this->checkPreAuth($user);
        } catch (CustomUserMessageAccountStatusException $exception) {
            $this->requestStack->getSession()->getFlashBag()->add(
                ApiMessages::INDEX_WARNING,
                '<strong>'.nl2br($exception->getMessage()).'</strong>'
            );
            // throw new RedirectException($this->router->generate('form_login'), message: $exception->getMessage());
        }
    }

    final public function checkPreAuth(UserInterface $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        !$user->hasPasswordChanged()
        && throw new CustomUserMessageAccountStatusException("<strong>Votre mot de passe doit être mis à jour avant de poursuivre.</strong>\n
                        Si vous n'avez pas reçu de lien d'activation
                        - veuillez vérifier vos emails dans le dossier \"spam\" ou \"indésirables\"
                        - sinon cliquez sur le lien <strong>\"mot de passe oublié\"</strong> pour en recevoir un nouveau");

        !$user->hasEmailVerified()
        && throw new CustomUserMessageAccountStatusException("Votre adresse e-mail n'a pas été validée.");

        !$user->isActivated()
        && throw new CustomUserMessageAccountStatusException('Votre compte est désactivé.');

        return true;
    }

    final public function checkPostAuth(UserInterface $user): void
    {
    }
}
