<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
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
    use UsernameableTrait;
    use RoleableTrait;

    public const ROLES = [
        self::ROLE_ADMIN => 'Administrateur',
        self::ROLE_USER => 'Utilisateur',
        self::ROLE_GUEST => 'Invité',
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

    #[ORM\OneToMany(mappedBy: 'userProject', targetEntity: Project::class, fetch: 'EAGER')]
    private Collection $projects;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
    }

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
        return $this->getUsername();
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

    final public function getProjects(): Collection
    {
        return $this->projects;
    }

    final public function addProject(Project $project): self
    {
        !$this->projects->contains($project)
            && $this->projects->add($project)
            && $project->setUserProject($this);

        return $this;
    }

    final public function removeProject(Project $project): self
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getUserProject() === $this) {
                $project->setUserProject(null);
            }
        }

        return $this;
    }
}
