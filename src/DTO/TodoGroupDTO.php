<?php

namespace RonasIT\ProjectInitializator\DTO;

use Illuminate\Support\Collection;

readonly class TodoGroupDTO
{
    /**
     * @param Collection<int, TodoItemDTO> $items
     */
    public function __construct(
        public ?string $subcategory,
        public Collection $items,
    ) {
    }
}
