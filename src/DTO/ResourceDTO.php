<?php

namespace RonasIT\ProjectInitializator\DTO;

class ResourceDTO
{
    public function __construct(
        protected string $title,
        protected bool $defaultUrl = false,
        protected ?string $link = null,
        protected bool $active = false,
    ) {}

    public function getTitle(): string
    {
        return $this->title;
    }

    public function hasDefaultUrl(): bool
    {
        return $this->defaultUrl;
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

    public function getLink(): ?string
    {
        return $this->link;
    }
}
