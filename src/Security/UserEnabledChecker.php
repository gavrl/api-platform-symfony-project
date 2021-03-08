<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;

class UserEnabledChecker implements UserCheckerInterface
{

    /**
     * @param UserInterface $user
     * @return mixed
     */
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isEnabled()) {
            throw new DisabledException();
        }
    }

    /**
     * @param UserInterface $user
     * @return mixed
     */
    public function checkPostAuth(UserInterface $user)
    {
    }
}