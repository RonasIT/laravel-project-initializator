<?php

namespace RonasIT\ProjectInitializator\Tests\Support\Traits;

use RonasIT\ProjectInitializator\DTO\TodoItemDTO;
use RonasIT\ProjectInitializator\Enums\TodoCategoryEnum;

trait TodoReporterTrait
{
    protected function assertSingleTodoItem(TodoCategoryEnum $category, string $label, ?string $hint): void
    {
        $groupedItems = $this->todoReporter->getItemsGroupedByCategory();

        $this->assertSame([$category->value], $groupedItems->keys()->all());
        $this->assertCount(1, $groupedItems[$category->value]);

        $item = $groupedItems[$category->value][0];

        $this->assertInstanceOf(TodoItemDTO::class, $item);
        $this->assertSame($category, $item->category);
        $this->assertSame($label, $item->label);
        $this->assertSame($hint, $item->hint);
    }
}
