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
        $this->addItem(TodoCategoryEnum::Readme, "Fill the {$name} link", $hint);
    }

    public function addReadmeField(string $name, ?string $hint = null): void
    {
        $this->addItem(TodoCategoryEnum::Readme, "Fill the {$name}", $hint);
    }

    public function addEnvVar(string $name, ?string $hint = null, string $file = '.env.development'): void
    {
        $this->addItem(TodoCategoryEnum::Environment, "Set the {$name} value in {$file}", $hint);
    }

    public function addConfiguration(string $integration, string $label, ?string $hint = null): void
    {
        $this->addItem(TodoCategoryEnum::Configuration, "{$integration}: {$label}", $hint);
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * @return Collection<string, Collection<int, TodoItemDTO>>
     */
    public function getItemsGroupedByCategory(): Collection
    {
        return collect(TodoCategoryEnum::cases())
            ->mapWithKeys(fn (TodoCategoryEnum $category) => [
                $category->value => $this->items
                    ->filter(fn (TodoItemDTO $item) => $item->category === $category)
                    ->values(),
            ])
            ->filter(fn (Collection $items) => $items->isNotEmpty());
    }

    protected function addItem(TodoCategoryEnum $category, string $label, ?string $hint = null): void
    {
        $this->items->push(new TodoItemDTO($category, $label, $hint));
    }
}
