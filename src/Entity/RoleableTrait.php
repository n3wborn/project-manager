<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait RoleableTrait
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_GUEST = 'ROLE_GUEST';

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    final public function getMainRole(): string
    {
        return current($this->getRoles());
    }

    /**
     * @see UserInterface
     */
    final public function getRoles(): array
    {
        // guarantee every user at least has ROLE_USER
        $roles = !empty($this->roles)
            ? $this->roles
            : [self::ROLE_USER];

        return array_unique($roles);
    }

    final public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    final public function addRole(string $role): self
    {
        $this->roles[] = $role;

        return $this;
    }

    final public function removeRole(string $role): self
    {
        if (($roleIndex = array_search($role, $this->getRoles(), true)) !== false) {
            unset($this->roles[$roleIndex]);
        }

        return $this;
    }
}
