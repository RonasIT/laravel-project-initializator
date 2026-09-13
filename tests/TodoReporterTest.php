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
        $this->todoReporter->addReadmeResourceLink('Issue Tracker', hint: 'ask the project manager for the tracker URL');

        $this->assertTodoItem(
            category: TodoCategoryEnum::Readme,
            label: 'Fill the Issue Tracker link',
            hint: 'ask the project manager for the tracker URL',
            subcategory: 'Resources',
        );
    }

    public function testAddReadmeContact(): void
    {
        $this->todoReporter->addReadmeContact("Manager's email", hint: 'ask the team lead');

        $this->assertTodoItem(
            category: TodoCategoryEnum::Readme,
            label: "Fill the Manager's email",
            hint: 'ask the team lead',
            subcategory: 'Contacts',
        );
    }

    public function testAddEnvVar(): void
    {
        $this->todoReporter->addEnvVar(
            name: 'CLERK_SECRET_KEY',
            file: '.env',
            hint: 'get it from the Clerk dashboard',
        );

        $this->assertTodoItem(
            category: TodoCategoryEnum::Environment,
            label: 'CLERK_SECRET_KEY',
            hint: 'get it from the Clerk dashboard',
            subcategory: '.env',
        );
    }

    public function testAddEnvVarReportedForMultipleFilesAppearsUnderEachFile(): void
    {
        $this->todoReporter->addEnvVar(
            name: 'CLERK_SECRET_KEY',
            file: '.env',
        );
        $this->todoReporter->addEnvVar(
            name: 'CLERK_SECRET_KEY',
            file: '.env.development',
        );

        $groupedItems = $this->todoReporter->getItemsGroupedByCategory();
        $subcategories = $groupedItems[TodoCategoryEnum::Environment->value];

        $this->assertSame(['.env', '.env.development'], $subcategories->keys()->all());
        $this->assertSame('CLERK_SECRET_KEY', $subcategories['.env'][0]->label);
        $this->assertSame('CLERK_SECRET_KEY', $subcategories['.env.development'][0]->label);
    }

    public function testAddConfiguration(): void
    {
        $this->todoReporter->addConfiguration('GCS', 'set the service account key', 'config/filesystems.php');

        $this->assertTodoItem(
            category: TodoCategoryEnum::Configuration,
            label: 'set the service account key',
            hint: 'config/filesystems.php',
            subcategory: 'GCS',
        );
    }

    public function testGetItemsGroupedByCategory(): void
    {
        $this->todoReporter->addEnvVar('GOOGLE_CLOUD_PROJECT_ID', '.env.development');
        $this->todoReporter->addReadmeResourceLink('Figma');
        $this->todoReporter->addReadmeContact("Manager's email");

        $groupedItems = $this->todoReporter->getItemsGroupedByCategory();

        $this->assertSame([
            TodoCategoryEnum::Readme->value,
            TodoCategoryEnum::Environment->value,
        ], $groupedItems->keys()->all());

        $readmeSubcategories = $groupedItems[TodoCategoryEnum::Readme->value];

        $this->assertEquals([
            new TodoItemDTO(
                category: TodoCategoryEnum::Readme,
                label: 'Fill the Figma link',
                subcategory: 'Resources',
            ),
        ], $readmeSubcategories['Resources']->all());

        $this->assertEquals([
            new TodoItemDTO(
                category: TodoCategoryEnum::Readme,
                label: "Fill the Manager's email",
                subcategory: 'Contacts',
            ),
        ], $readmeSubcategories['Contacts']->all());

        $this->assertEquals([
            new TodoItemDTO(
                category: TodoCategoryEnum::Environment,
                label: 'GOOGLE_CLOUD_PROJECT_ID',
                subcategory: '.env.development',
            ),
        ], $groupedItems[TodoCategoryEnum::Environment->value]['.env.development']->all());
    }
}
