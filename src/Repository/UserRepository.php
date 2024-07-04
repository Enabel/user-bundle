<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Enabel\UserBundle\Entity\User;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use TheNetworg\OAuth2\Client\Provider\AzureResourceOwner;

abstract class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        // @codeCoverageIgnoreStart
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }
        // @codeCoverageIgnoreEnd

        $user->setPassword($newHashedPassword);

        $this->add($user, true);
    }

    /**
     * Update last login datetime
     *
     * @throws \Exception
     * @throws \Doctrine\DBAL\Exception
     * @codeCoverageIgnore
     */
    final public function setLastLogin(User $user): void
    {
        // Don't use ORM to avoid wrong updated date
        $sql = 'UPDATE user set last_login_at = :lastLoginAt where id = :id';
        //set parameters
        $params = [];
        $params['lastLoginAt'] = (new \DateTime())->format('Y-m-d H:i:s');
        $params['id'] = $user->getId();
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->executeStatement($params);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    final public function findOrCreateFromAzure(AzureResourceOwner $owner): User
    {
        if ($owner->getUpn() !== null) {
            /** @var User|null $user */
            $user = $this->findOneBy(['email' => $owner->getUpn()]);

            if ($user === null) {
                // create the user
                $userClass = $this->getClassName();
                /** @var User $user */
                $user = new $userClass();
                $locale = User::DEFAULT_LOCALE;
                $user
                    ->setPlainPassword(uniqid('password', true))
                    ->setLocale($locale)
                ;
            }

            // Update info
            if ($owner->claim('displayName') !== null && is_string($owner->claim('displayName'))) {
                $displayName = $owner->claim('displayName');
            } else {
                $displayName = $owner->getUpn();
            }

            if ($owner->claim('jobTitle') !== null && is_string($owner->claim('jobTitle'))) {
                $user->setJobTitle($owner->claim('jobTitle'));
            }

            if ($owner->claim('country') !== null && is_string($owner->claim('country'))) {
                $user->setCountryWorkplace($owner->claim('country'));
            }

            if ($owner->claim('language') !== null && is_string($owner->claim('language'))) {
                $locale = strtolower(substr($owner->claim('language'), 0, 2));
                if (empty($user->getLocale())) {
                    $user->setLocale($locale);
                }
            }

            $user
                ->setEmail($owner->getUpn())
                ->setDisplayName($displayName)
            ;

            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();

            return $user;
        }

        throw new InvalidArgumentException('Upn cannot be null');
    }

    /**
     * @return array<int, string>
     */
    public function suggestionsAutocompleteEmail(): array
    {
        $suggestions = [];
        /** @var array<User> $users */
        $users = $this->findAll();
        foreach ($users as $user) {
            if ($user->getEmail() !== null) {
                $suggestions[] = $user->getEmail();
            }
        }

        return $suggestions;
    }
}
