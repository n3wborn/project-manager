<?php

namespace App\Service\User;

final class UserFetchDTO
{
    public function __construct(
        private string $email = '',
        private string $username = '',
        private string $slug = '',
        private array $projects = [],
    ) {
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getProjects(): array
    {
        return $this->projects;
    }

    public function setProjects(array $projects): self
    {
        $this->projects = $projects;

        return $this;
    }
}
