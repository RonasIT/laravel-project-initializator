<?php

namespace RonasIT\ProjectInitializator\Tests\Support\Traits;

use RonasIT\ProjectInitializator\DTO\TodoItemDTO;
use RonasIT\ProjectInitializator\Enums\TodoCategoryEnum;

trait TodoReporterTrait
{
    protected function assertTodoItem(
        TodoCategoryEnum $category,
        string $label,
        ?string $hint = null,
        string $subcategory = '',
    ): void {
        $groupedItems = $this->todoReporter->getItemsGroupedByCategory();

        $this->assertSame([$category->value], $groupedItems->keys()->all());

        $subcategories = $groupedItems[$category->value];

        $this->assertSame([$subcategory], $subcategories->keys()->all());
        $this->assertCount(1, $subcategories[$subcategory]);

        $item = $subcategories[$subcategory][0];

        $this->assertInstanceOf(TodoItemDTO::class, $item);
        $this->assertSame($category, $item->category);
        $this->assertSame($label, $item->label);
        $this->assertSame($hint, $item->hint);
        $this->assertSame($subcategory, $item->subcategory);
    }
}
