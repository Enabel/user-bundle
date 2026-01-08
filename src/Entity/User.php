<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Enabel\UserBundle\Repository\UserRepository;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[UniqueEntity('email')]
#[ORM\MappedSuperclass(repositoryClass: UserRepository::class)]
#[ORM\Table(options: ['comment' => 'Table of users'])]
class User implements
    UserInterface,
    PasswordAuthenticatedUserInterface
{
    use TimestampableEntity;
    use BlameableEntity;
    use SoftDeleteableEntity;

    public const DEFAULT_LOCALE = 'en';

    public const ADMIN = 'ROLE_ADMIN';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['comment' => 'Unique identifier of the user'])]
    protected int $id;

    #[ORM\Column(type: 'string', nullable: true, options: ['comment' => 'Password hash'])]
    protected ?string $password = null;

    #[ORM\Column(type: 'string', length: 255, options: ['comment' => 'Display name of the user'])]
    private string $displayName;

    #[ORM\Column(type: 'string', length: 180, unique: true, options: ['comment' => 'Email of the user'])]
    private string $email;

    /** @var array<string>  */
    #[ORM\Column(type: 'json', options: ['comment' => 'Roles of the user'])]
    private array $roles = [];

    #[ORM\Column(type: 'string', nullable: true, options: ['comment' => 'Job title of the user'])]
    private ?string $jobTitle = null;

    #[ORM\Column(type: 'string', nullable: true, options: ['comment' => 'Country workplace of the user'])]
    private ?string $countryWorkplace = null;

    private ?string $plainPassword = null;

    #[ORM\Column(type: 'datetime', nullable: true, options: ['comment' => 'Last login time of the user'])]
    private ?\DateTimeInterface $lastLoginAt;

    #[ORM\Column(type: 'string', length: 2, nullable: true, options: ['comment' => 'Locale of the user'])]
    private ?string $locale;

    public function __construct()
    {
        $this->locale = self::DEFAULT_LOCALE;
    }

    public function __toString(): string
    {
        return $this->getDisplayName();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        /** @phpstan-ignore-next-line */
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     * @return array<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): self
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string $role): self
    {
        if ($this->hasRole($role)) {
            $this->roles = array_diff($this->roles, [$role]);
        }

        return $this;
    }

    public function hasRole(string $role): bool
    {
        if (in_array($role, $this->getRoles(), true)) {
            return true;
        }

        return false;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     * @codeCoverageIgnore
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getSalt(): ?string
    {
        return 'EnabelSalt';
    }

    public function getLastLoginAt(): ?\DateTimeInterface
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeInterface $lastLoginAt): void
    {
        $this->lastLoginAt = $lastLoginAt;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $password): self
    {
        $this->plainPassword = $password;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(?string $jobTitle): self
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function getCountryWorkplace(): ?string
    {
        return $this->countryWorkplace;
    }

    public function setCountryWorkplace(?string $countryWorkplace): self
    {
        $this->countryWorkplace = $countryWorkplace;

        return $this;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return $this->email === $user->getUserIdentifier();
    }

    public function serialize(): string
    {
        return serialize([
            $this->getId(),
            $this->getUserIdentifier(),
            $this->getDisplayName(),
            $this->getRoles(),
            $this->getPassword(),
        ]);
    }

    public function restore(): void
    {
        $this->setDeletedAt(null);
    }
}
