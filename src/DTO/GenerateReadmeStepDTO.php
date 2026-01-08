<?php

namespace RonasIT\ProjectInitializator\DTO;

use Closure;

readonly class GenerateReadmeStepDTO
{
    public function __construct(
        public string $question,
        public Closure $action,
    ) {
    }
}
