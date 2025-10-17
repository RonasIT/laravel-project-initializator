<?php

namespace RonasIT\ProjectInitializator\DTO;

class ReadmeDataDTO
{
    public function __construct(
        public readonly string $gitProjectPath,
        public readonly string $appName,
        public readonly string $appType,
    ) {}
}