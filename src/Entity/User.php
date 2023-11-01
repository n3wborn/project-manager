<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use ArchivableEntity;
    use SluggableTrait;
    use CreatableUpdateableTrait;

    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_USER = 'ROLE_USER';
    public const ROLES = [
        self::ROLE_ADMIN => 'Administrateur',
        self::ROLE_USER => 'Utilisateur',
    ];

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    final public function getId(): ?Uuid
    {
        return $this->id;
    }

    final public function getEmail(): ?string
    {
        return $this->email;
    }

    final public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /** @see UserInterface */
    final public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /** @see UserInterface */
    final public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    final public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /** @see PasswordAuthenticatedUserInterface  */
    final public function getPassword(): string
    {
        return $this->password;
    }

    final public function setPassword(#[\SensitiveParameter] string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /** @see UserInterface */
    final public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->setUser($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getUser() === $this) {
                $project->setUser(null);
            }
        }

        return $this;
    }
}
