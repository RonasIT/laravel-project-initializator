<?php

namespace RonasIT\ProjectInitializator\DTO;

final class ContactDTO
{
    public function __construct(
        public readonly string $title,
        protected ?string $email = null,
    ) {
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
