<?php

namespace RonasIT\ProjectInitializator\Support;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
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
            label: $label ?? "Fill {$name}",
            hint: $hint ?? 'in README',
        );
    }

    public function addReadmeResourceLink(string $name, ?string $label = null, ?string $hint = null): void
    {
        $this->addItem(
            category: TodoCategoryEnum::Readme,
            label: $label ?? "Fill {$name} link",
            hint: $hint ?? 'in README',
        );
    }

    public function addEnvVariable(string $name, ?string $hint = null, string $file = '.env'): void
    {
        $this->addItem(
            category: TodoCategoryEnum::Environment,
            label: $name,
            hint: $hint ?? "in {$file}",
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

    public function render(Command $output): void
    {
        if ($this->items->isEmpty()) {
            return;
        }

        $output->warn('Don`t forget to finish the setup:');

        $byCategory = $this->items->groupBy(fn (TodoItemDTO $item) => $item->category->value);

        foreach ($byCategory as $categoryValue => $items) {
            $output->newLine();
            $output->warn(TodoCategoryEnum::from($categoryValue)->label() . ':');

            $bySubcategory = $items->groupBy(fn (TodoItemDTO $item) => $item->subcategory ?? '');

            foreach ($bySubcategory as $subcategory => $subItems) {
                $indent = '  ';

                if ($subcategory !== '') {
                    $output->warn("  {$subcategory}:");

                    $indent = '    ';
                }

                foreach ($subItems as $item) {
                    $line = "{$indent}- {$item->label}";

                    if (!empty($item->hint)) {
                        $line .= " ({$item->hint})";
                    }

                    $output->warn($line);
                }
            }
        }
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
