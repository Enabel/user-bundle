<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Service\Account;

use Enabel\UserBundle\Entity\User;
use Enabel\UserBundle\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ChangePassword
{
    private UserPasswordHasherInterface $passwordHasher;
    private UserRepository $userRepository;

    public function __construct(UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository)
    {
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
    }

    /**
     * @return bool Return true if password successfully changed, false otherwise.
     */
    public function __invoke(User $user, string $oldPassword, string $newPassword): bool
    {
        if ($this->passwordHasher->isPasswordValid($user, $oldPassword)) {
            $this->userRepository->upgradePassword($user, $this->passwordHasher->hashPassword($user, $newPassword));

            return true;
        }

        return false;
    }
}
