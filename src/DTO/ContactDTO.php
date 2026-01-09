<?php

namespace RonasIT\ProjectInitializator\DTO;

final class ContactDTO
{
    public function __construct(
        private string $title,
        private ?string $email = null,
    ) {}

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
