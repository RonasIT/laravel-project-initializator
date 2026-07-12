<?php

namespace RonasIT\ProjectInitializator\Tests;

use RonasIT\ProjectInitializator\DTO\TodoItemDTO;
use RonasIT\ProjectInitializator\Enums\TodoCategoryEnum;
use RonasIT\ProjectInitializator\Support\TodoReporter;
use RonasIT\ProjectInitializator\Tests\Support\Traits\TodoReporterTrait;

class TodoReporterTest extends TestCase
{
    use TodoReporterTrait;

    protected TodoReporter $todoReporter;

    public function setUp(): void
    {
        parent::setUp();

        $this->todoReporter = new TodoReporter();
    }

    public function testIsEmptyWithoutItems(): void
    {
        $this->assertTrue($this->todoReporter->isEmpty());
        $this->assertEmpty($this->todoReporter->getItemsGroupedByCategory());
    }

    public function testAddReadmeResourceLink(): void
    {
        $this->todoReporter->addReadmeResourceLink('Issue Tracker');

        $this->assertSingleTodoItem(TodoCategoryEnum::Readme, 'Fill the Issue Tracker link', null);
    }

    public function testAddReadmeField(): void
    {
        $this->todoReporter->addReadmeField("Manager's email");

        $this->assertSingleTodoItem(TodoCategoryEnum::Readme, "Fill the Manager's email", null);
    }

    public function testAddEnvVar(): void
    {
        $this->todoReporter->addEnvVar('CLERK_SECRET_KEY');

        $this->assertSingleTodoItem(TodoCategoryEnum::Environment, 'Set the CLERK_SECRET_KEY value in .env.development', null);
    }

    public function testAddEnvVarWithCustomFile(): void
    {
        $this->todoReporter->addEnvVar(
            name: 'GOOGLE_CLOUD_PROJECT_ID',
            file: '.env.example',
        );

        $this->assertSingleTodoItem(TodoCategoryEnum::Environment, 'Set the GOOGLE_CLOUD_PROJECT_ID value in .env.example', null);
    }

    public function testAddEnvVarWithCustomHint(): void
    {
        $this->todoReporter->addEnvVar(
            name: 'CLERK_SECRET_KEY',
            hint: 'get it from the Clerk dashboard',
            file: '.env',
        );

        $this->assertSingleTodoItem(
            category: TodoCategoryEnum::Environment,
            label: 'Set the CLERK_SECRET_KEY value in .env',
            hint: 'get it from the Clerk dashboard',
        );
    }

    public function testAddConfiguration(): void
    {
        $this->todoReporter->addConfiguration('GCS', 'set the service account key', 'config/filesystems.php');

        $this->assertSingleTodoItem(TodoCategoryEnum::Configuration, 'GCS: set the service account key', 'config/filesystems.php');
    }

    public function testGetItemsGroupedByCategory(): void
    {
        $this->todoReporter->addEnvVar('GOOGLE_CLOUD_PROJECT_ID');
        $this->todoReporter->addReadmeResourceLink('Figma');
        $this->todoReporter->addReadmeField("Manager's email");

        $groupedItems = $this->todoReporter->getItemsGroupedByCategory();

        $this->assertSame([
            TodoCategoryEnum::Readme->value,
            TodoCategoryEnum::Environment->value,
        ], $groupedItems->keys()->all());

        $this->assertEquals([
            new TodoItemDTO(TodoCategoryEnum::Readme, 'Fill the Figma link'),
            new TodoItemDTO(TodoCategoryEnum::Readme, "Fill the Manager's email"),
        ], $groupedItems[TodoCategoryEnum::Readme->value]->all());

        $this->assertEquals([
            new TodoItemDTO(TodoCategoryEnum::Environment, 'Set the GOOGLE_CLOUD_PROJECT_ID value in .env.development'),
        ], $groupedItems[TodoCategoryEnum::Environment->value]->all());
    }
}
