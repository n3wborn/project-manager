<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait UsernameableTrait
{
    #[ORM\Column(type: 'string', length: 80, unique: true)]
    private ?string $username = null;

    final public function getUsername(): string
    {
        return $this->username;
    }

    final public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }
}
