<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Security;

use Enabel\UserBundle\Entity\User;
use Enabel\UserBundle\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(protected UserRepository $repository)
    {
    }

    /**
     * The loadUserByIdentifier() method was introduced in Symfony 5.3.
     * In previous versions it was called loadUserByUsername().
     *
     * Symfony calls this method if you use features like switch_user
     * or remember_me. If you're not using these features, you do not
     * need to implement this method.
     *
     * @throws UserNotFoundException if the user is not found
     * @throws CustomUserMessageAccountStatusException if the user is deleted (SoftDelete)
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->repository->findOneBy(['email' => $identifier]);

        if (!$user instanceof \Enabel\UserBundle\Entity\User) {
            $e = new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
            $e->setUserIdentifier($identifier);

            throw $e;
        }

        if ($user->isDeleted()) {
            throw new CustomUserMessageAccountStatusException(sprintf('User "%s" is deleted.', $identifier));
        }

        return $user;
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     *
     * @param User $user
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        /** @var string $email */
        $email = $user->getEmail();
        return $this->loadUserByIdentifier($email);
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    /**
     * Upgrades the hashed password of a user, typically for using a better hash algorithm.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if ($this->repository instanceof PasswordUpgraderInterface) {
            $this->repository->upgradePassword($user, $newHashedPassword);
        }
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }
}
