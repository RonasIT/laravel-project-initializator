<?php

namespace RonasIT\ProjectInitializator\Support;

use Illuminate\Support\Collection;
use RonasIT\ProjectInitializator\DTO\TodoItemDTO;
use RonasIT\ProjectInitializator\Enums\TodoCategoryEnum;

class TodoReporter
{
    protected Collection $items;

    public function __construct()
    {
        $this->items = collect();
    }

    public function addReadmeResourceLink(string $name, ?string $hint = null): void
    {
        $this->addItem(
            category: TodoCategoryEnum::Readme,
            label: "Fill the {$name} link",
            subcategory: 'Resources',
            hint: $hint,
        );
    }

    public function addReadmeContact(string $name, ?string $hint = null): void
    {
        $this->addItem(
            category: TodoCategoryEnum::Readme,
            label: "Fill the {$name}",
            subcategory: 'Contacts',
            hint: $hint,
        );
    }

    public function addEnvVar(string $name, string $file, ?string $hint = null): void
    {
        $this->addItem(
            category: TodoCategoryEnum::Environment,
            label: $name,
            subcategory: $file,
            hint: $hint,
        );
    }

    public function addConfiguration(string $integration, string $label, ?string $hint = null): void
    {
        $this->addItem(
            category: TodoCategoryEnum::Configuration,
            label: $label,
            subcategory: $integration,
            hint: $hint,
        );
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    public function getItemsGroupedByCategory(): Collection
    {
        return collect(TodoCategoryEnum::cases())
            ->mapWithKeys(fn (TodoCategoryEnum $category) => [
                $category->value => $this->items
                    ->filter(fn (TodoItemDTO $item) => $item->category === $category)
                    ->groupBy(fn (TodoItemDTO $item) => $item->subcategory)
                    ->map(fn (Collection $items) => $items->values()),
            ])
            ->filter(fn (Collection $subcategories) => $subcategories->isNotEmpty());
    }

    public function getReport(): string
    {
        $lines = ["Don't forget to complete the following steps:"];

        foreach ($this->getItemsGroupedByCategory() as $category => $subcategories) {
            $lines[] = '';
            $lines[] = "{$category}:";

            foreach ($subcategories as $subcategory => $items) {
                $lines[] = "  {$subcategory}:";

                foreach ($items as $item) {
                    $lines[] = "    - {$item->label}" . ($item->hint ? " ({$item->hint})" : '');
                }
            }
        }

        return implode("\n", $lines);
    }

    protected function addItem(
        TodoCategoryEnum $category,
        string $label,
        string $subcategory,
        ?string $hint = null,
    ): void {
        $item = new TodoItemDTO($category, $label, $subcategory, $hint);

        if ($this->items->doesntContain($item)) {
            $this->items->push($item);
        }
    }
}
