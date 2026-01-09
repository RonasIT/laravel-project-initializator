<?php

namespace RonasIT\ProjectInitializator\DTO;

final class CredentialDTO
{
    public function __construct(
        private string $title,
        private ?string $email = null,
        private ?string $password = null,
    ) {}

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}
