<?php

namespace RonasIT\ProjectInitializator\DTO;

final class CredentialDTO
{
    public function __construct(
        public readonly string $title,
        protected ?string $email = null,
        protected ?string $password = null,
    ) {
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
