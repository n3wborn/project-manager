<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\Table(name: 'project')]
#[ORM\HasLifecycleCallbacks]
class Project
{
    use ArchivableEntity;
    use SluggableTrait;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true, type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToMany(targetEntity: Category::class, mappedBy: 'projects', cascade: ['persist'], fetch: 'EAGER', orphanRemoval: true)]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    final public function getId(): ?Uuid
    {
        return $this->id;
    }

    final public function getName(): ?string
    {
        return $this->name;
    }

    final public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    final public function getDescription(): ?string
    {
        return $this->description;
    }

    final public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    final public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    final public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    final public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    final public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PrePersist]
    final public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    final public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        !$this->categories->contains($category)
            && $this->categories->add($category)
            && $category->addProject($this);

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category)
        && $category->removeProject($this);

        return $this;
    }
}
