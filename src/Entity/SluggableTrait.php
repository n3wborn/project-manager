<?php

namespace App\Entity;

use App\Helper\Randomizer;
use Doctrine\ORM\Mapping as ORM;

trait SluggableTrait
{
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $slug = null;

    final public function getSlug(): string
    {
        return $this->slug;
    }

    final public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    #[ORM\PrePersist]
    final public function generateSlug(): void
    {
        null === $this->slug
        && $this->setSlug(Randomizer::generateSlug());
    }
}
