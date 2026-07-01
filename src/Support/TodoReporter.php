<?php

namespace RonasIT\ProjectInitializator\Support;

use Illuminate\Support\Collection;
use RonasIT\ProjectInitializator\DTO\TodoGroupDTO;
use RonasIT\ProjectInitializator\DTO\TodoItemDTO;
use RonasIT\ProjectInitializator\Enums\TodoCategoryEnum;

class TodoReporter
{
    /** @var Collection<int, TodoItemDTO> */
    protected Collection $items;

    public function __construct()
    {
        $this->items = collect();
    }

    public function addReadmeField(string $name, ?string $label = null, ?string $hint = null): void
    {
        $this->addItem(
            category: TodoCategoryEnum::Readme,
            label: $label ?? $name,
            hint: $hint,
        );
    }

    public function addReadmeResourceLink(string $name, ?string $label = null, ?string $hint = null): void
    {
        $this->addItem(
            category: TodoCategoryEnum::Readme,
            label: $label ?? "{$name} link",
            hint: $hint,
        );
    }

    public function addEnvVar(string $name, ?string $hint = null, string $file = '.env'): void
    {
        if ($file !== '.env') {
            $hint = ($hint === null) ? "in {$file}" : "{$hint}, in {$file}";
        }

        $this->addItem(
            category: TodoCategoryEnum::Environment,
            label: $name,
            hint: $hint,
        );
    }

    /**
     * @param  string[]  $names
     */
    public function addEnvVars(array $names, ?string $hint = null, string $file = '.env'): void
    {
        foreach ($names as $name) {
            $this->addEnvVar($name, $hint, $file);
        }
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

    /**
     * @return Collection<string, Collection<int, TodoGroupDTO>>
     */
    public function getGrouped(): Collection
    {
        return collect(TodoCategoryEnum::cases())
            ->mapWithKeys(fn (TodoCategoryEnum $category) => [
                $category->value => $this->items->filter(fn (TodoItemDTO $item) => $item->category === $category),
            ])
            ->filter(fn (Collection $items) => $items->isNotEmpty())
            ->map(fn (Collection $items) => $items
                ->groupBy(fn (TodoItemDTO $item) => $item->subcategory ?? '')
                ->map(fn (Collection $groupItems, string $key) => new TodoGroupDTO(
                    subcategory: ($key === '') ? null : $key,
                    items: $groupItems->values(),
                ))
                ->values());
    }

    protected function addItem(
        TodoCategoryEnum $category,
        string $label,
        ?string $hint = null,
        ?string $subcategory = null,
    ): void {
        $this->items->push(new TodoItemDTO(
            category: $category,
            label: $label,
            hint: $hint,
            subcategory: $subcategory,
        ));
    }
}
