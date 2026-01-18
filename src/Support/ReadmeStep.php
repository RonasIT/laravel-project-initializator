<?php

namespace RonasIT\ProjectInitializator\Support;

use Closure;

final readonly class ReadmeStep
{
    public function __construct(
        public string $question,
        public Closure $action,
    ) {
    }
}
