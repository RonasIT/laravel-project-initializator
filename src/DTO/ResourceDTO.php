<?php

namespace RonasIT\ProjectInitializator\DTO;

class ResourceDTO
{
    public function __construct(
        public readonly string $key,
        public readonly string $title,
        public readonly ?string $localPath = null,
        protected ?string $link = null,
        protected bool $active = false,
        protected ?string $email = null,
        protected ?string $password = null,
    ) {
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): void
    {
        $this->link = $link;
        $this->active = ($link !== 'no');
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setCredentials(string $email, string $password): void
    {
        $this->email = $email;
        $this->password = $password;
    }
}
