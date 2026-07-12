<?php

namespace RonasIT\ProjectInitializator\Tests\Support\Traits;

use RonasIT\ProjectInitializator\DTO\TodoItemDTO;
use RonasIT\ProjectInitializator\Enums\TodoCategoryEnum;

trait TodoReporterTrait
{
    protected function assertTodoItem(TodoCategoryEnum $category, string $label, ?string $hint): void
    {
        $this->assertFalse($this->todoReporter->isEmpty());

        $groupedItems = $this->todoReporter->getItemsGroupedByCategory();

        $this->assertArrayHasKey($category->value, $groupedItems);
        $this->assertCount(1, $groupedItems[$category->value]);

        $item = $groupedItems[$category->value][0];

        $this->assertInstanceOf(TodoItemDTO::class, $item);
        $this->assertSame($category, $item->category);
        $this->assertSame($label, $item->label);
        $this->assertSame($hint, $item->hint);
    }
}
