<?php

namespace App\Security;

use App\Entity\Participant;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{

    /**
     * @inheritDoc
     */
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof Participant) {
            return;
        }
        if(!$user->getActif()){
            throw new CustomUserMessageAccountStatusException('Votre comptes est désactiver');
        }
    }

    /**
     * @inheritDoc
     */
    public function checkPostAuth(UserInterface $user)
    {
        if (!$user instanceof Participant) {
            return;
        }
        if(!$user->getActif()){
            throw new CustomUserMessageAccountStatusException('Votre comptes est désactiver');
        }
    }
}