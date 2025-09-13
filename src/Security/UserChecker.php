<?php

namespace App\Security;

use App\Entity\Account;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof Account) {
            return;
        }

        // check: email verified
        if (method_exists($user, 'isVerified') && !$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException(
                'Please verify your email address before logging in. A confirmation email was sent to you during registration.'
            );
        }

        // Optional: other checks (banned, suspended, etc.)
        // if (method_exists($user, 'isBanned') && $user->isBanned()) {
        //     throw new CustomUserMessageAccountStatusException(
        //         'Your account has been banned.'
        //     );
        // }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
