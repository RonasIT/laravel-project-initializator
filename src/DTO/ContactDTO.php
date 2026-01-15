<?php

namespace RonasIT\ProjectInitializator\DTO;

final class ContactDTO
{
    public function __construct(
        public readonly string $key,
        public readonly string $title,
        public private(set) ?string $email = null,
    ) {
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
