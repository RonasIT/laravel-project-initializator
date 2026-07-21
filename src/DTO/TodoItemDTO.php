<?php

namespace RonasIT\ProjectInitializator\DTO;

use RonasIT\ProjectInitializator\Enums\TodoCategoryEnum;

readonly class TodoItemDTO
{
    public function __construct(
        public TodoCategoryEnum $category,
        public string $label,
        public ?string $hint = null,
        public ?string $subcategory = null,
    ) {
    }
}
