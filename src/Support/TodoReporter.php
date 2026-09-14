<?php

namespace RonasIT\ProjectInitializator\Support;

use Illuminate\Support\Collection;
use RonasIT\ProjectInitializator\DTO\TodoItemDTO;
use RonasIT\ProjectInitializator\Enums\TodoCategoryEnum;

class TodoReporter
{
    /**
     * @var Collection<int, TodoItemDTO>
     */
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
            hint: $hint,
            subcategory: 'Resources',
        );
    }

    public function addReadmeContact(string $name, ?string $hint = null): void
    {
        $this->addItem(
            category: TodoCategoryEnum::Readme,
            label: "Fill the {$name}",
            hint: $hint,
            subcategory: 'Contacts',
        );
    }

    public function addEnvVar(string $name, string $file, ?string $hint = null): void
    {
        $this->addItem(
            category: TodoCategoryEnum::Environment,
            label: $name,
            hint: $hint,
            subcategory: $file,
        );
    }

    public function addConfiguration(string $integration, string $label, ?string $hint = null): void
    {
        $this->addItem(
            category: TodoCategoryEnum::Configuration,
            label: $label,
            hint: $hint,
            subcategory: $integration,
        );
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * @return Collection<string, Collection<string, Collection<int, TodoItemDTO>>>
     */
    public function getItemsGroupedByCategory(): Collection
    {
        return collect(TodoCategoryEnum::cases())
            ->mapWithKeys(fn (TodoCategoryEnum $category) => [
                $category->value => $this->items
                    ->filter(fn (TodoItemDTO $item) => $item->category === $category)
                    ->groupBy(fn (TodoItemDTO $item) => $item->subcategory ?? '')
                    ->map(fn (Collection $items) => $items->values()),
            ])
            ->filter(fn (Collection $subcategories) => $subcategories->isNotEmpty());
    }

    protected function addItem(
        TodoCategoryEnum $category,
        string $label,
        ?string $hint = null,
        ?string $subcategory = null,
    ): void {
        $item = new TodoItemDTO($category, $label, $hint, $subcategory);

        if ($this->items->doesntContain($item)) {
            $this->items->push($item);
        }
    }
}
