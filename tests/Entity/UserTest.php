<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Tests\Entity;

use DateTime;
use Enabel\UserBundle\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testSetterGetter(): void
    {
        // Get data
        $data = $this->getData();

        // Create entity with data
        $entity = $this->createEntityWithData($data);

        // Test data
        $this->assertSame($entity->getId(), $data['id']);
        $this->assertSame($entity->getEmail(), $data['email']);
        $this->assertSame($entity->getUserIdentifier(), $data['email']);
        $this->assertSame($entity->getDisplayName(), $data['displayName']);
        $this->assertSame($entity->getPassword(), $data['password']);
        $this->assertSame($entity->getPlainPassword(), $data['plainPassword']);
        $this->assertSame($entity->getRoles(), $data['roles']);
        $this->assertSame($entity->getLastLoginAt(), $data['lastLoginAt']);
        $this->assertSame($entity->getCreatedAt(), $data['createdAt']);
        $this->assertSame($entity->getCreatedBy(), $data['createdBy']);
        $this->assertSame($entity->getUpdatedAt(), $data['updatedAt']);
        $this->assertSame($entity->getUpdatedBy(), $data['updatedBy']);
        $this->assertSame($entity->getDeletedAt(), $data['deletedAt']);
    }

    public function testRestoreIsWorking(): void
    {
        // Get data
        $data = $this->getData();

        // Create entity with data
        $entity = $this->createEntityWithData($data);

        // test data
        $this->assertSame($entity->getDeletedAt(), $data['deletedAt']);

        // restore
        $entity->restore();

        // test restored
        $this->assertNull($entity->getDeletedAt());
    }

    /**
     * @param array{
     *     id: int,
     *     email: string,
     *     displayName: string,
     *     plainPassword: string,
     *     password: string,
     *     roles: array<string>,
     *     lastLoginAt: DateTime,
     *     createdAt: DateTime,
     *     createdBy: int,
     *     updatedAt: DateTime,
     *     updatedBy: int,
     *     deletedAt: DateTime
     * } $data
     */
    private function createEntityWithData(array $data): User
    {
        // Create entity
        $user = new User();

        // Set data
        $user->setId($data['id']);
        $user->setEmail($data['email']);
        $user->setDisplayName($data['displayName']);
        $user->setPassword($data['password']);
        $user->setPlainPassword($data['plainPassword']);
        $user->setRoles($data['roles']);

        $user->setLastLoginAt($data['lastLoginAt']);
        $user->setCreatedBy($data['createdBy']);
        $user->setCreatedAt($data['createdAt']);
        $user->setUpdatedBy($data['updatedBy']);
        $user->setUpdatedAt($data['updatedAt']);
        $user->setDeletedAt($data['deletedAt']);

        return $user;
    }

    /**
     * @return array{
     *     id: int,
     *     email: string,
     *     displayName: string,
     *     plainPassword: string,
     *     password: string,
     *     roles: array<string>,
     *     lastLoginAt: DateTime,
     *     createdAt: DateTime,
     *     createdBy: int,
     *     updatedAt: DateTime,
     *     updatedBy: int,
     *     deletedAt: DateTime
     * }
     */
    private function getData(): array
    {
        return [
            'id' => 1,
            'email' => 'firtsname.lastname@email.com',
            'displayName' => 'DisplayName LASTNAME',
            'plainPassword' => 'P@ssw0rd',
            'password' => '$2y$13$RAGjzapqWn0zAIPyRXVuReeURlm63WAldEDxHCtHV2n2.90mwY.z.',
            'roles' => ['ROLE_USER', 'ROLE_ADMIN'],
            'lastLoginAt' => new \DateTime('now'),
            'createdAt' => new \DateTime('yesterday'),
            'createdBy' => 1,
            'updatedAt' => new \DateTime('now'),
            'updatedBy' => 2,
            'deletedAt' => new \DateTime('now'),
        ];
    }
}
