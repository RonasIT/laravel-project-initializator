<?php

namespace RonasIT\ProjectInitializator\DTO;

class ResourceDTO
{
    public function __construct(
        public readonly string $key,
        public readonly string $title,
        public readonly ?string $localPath = null,
        public private(set) ?string $link = null,
        protected bool $active = false,
        public private(set) ?string $email = null,
        public private(set) ?string $password = null,
    ) {
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

    public function setCredentials(string $email, string $password): void
    {
        $this->email = $email;
        $this->password = $password;
    }
}
