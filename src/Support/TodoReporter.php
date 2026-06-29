<?php

namespace RonasIT\ProjectInitializator\Support;

use Illuminate\Console\Command;
use RonasIT\ProjectInitializator\DTO\TodoItemDTO;
use RonasIT\ProjectInitializator\Enums\TodoCategoryEnum;

class TodoReporter
{
    /** @var TodoItemDTO[] */
    protected array $items = [];

    public function addReadmeField(
        string $name,
        ?string $label = null,
        ?string $hint = null,
        array $meta = [],
    ): void {
        $this->addItem(
            category: TodoCategoryEnum::Readme,
            label: $label ?? "Fill {$name}",
            hint: $hint ?? 'in README',
            meta: array_merge(['type' => 'field'], $meta),
        );
    }

    public function addReadmeResourceLink(
        string $name,
        ?string $label = null,
        ?string $hint = null,
        array $meta = [],
    ): void {
        $this->addItem(
            category: TodoCategoryEnum::Readme,
            label: $label ?? "Fill {$name} link",
            hint: $hint ?? 'in README',
            meta: array_merge(['type' => 'link'], $meta),
        );
    }

    public function addEnvVariable(
        string $name,
        ?string $hint = null,
        string $file = '.env',
        array $meta = [],
    ): void {
        $this->addItem(
            category: TodoCategoryEnum::Environment,
            label: $name,
            hint: $hint ?? "in {$file}",
            meta: array_merge(['file' => $file], $meta),
        );
    }

    public function addConfiguration(
        string $integration,
        string $label,
        ?string $hint = null,
        array $meta = [],
    ): void {
        $this->addItem(
            category: TodoCategoryEnum::Configuration,
            label: $label,
            hint: $hint,
            meta: array_merge(['integration' => $integration], $meta),
        );
    }

    public function render(Command $output): void
    {
        if (empty($this->items)) {
            return;
        }

        $output->warn('Don`t forget to finish the setup:');

        foreach ($this->grouped() as $categoryValue => $items) {
            $category = TodoCategoryEnum::from($categoryValue);

            $output->newLine();
            $output->warn("{$category->label()}:");

            foreach ($items as $item) {
                $line = "  - {$item->label}";

                if (!empty($item->hint)) {
                    $line .= " ({$item->hint})";
                }

                $output->warn($line);
            }
        }
    }

    protected function addItem(
        TodoCategoryEnum $category,
        string $label,
        ?string $hint = null,
        array $meta = [],
    ): void {
        $this->items[] = new TodoItemDTO(
            category: $category,
            label: $label,
            hint: $hint,
            meta: $meta,
        );
    }

    /**
     * @return array<string, TodoItemDTO[]>
     */
    protected function grouped(): array
    {
        $grouped = [];

        foreach ($this->items as $item) {
            $grouped[$item->category->value][] = $item;
        }

        return $grouped;
    }
}
