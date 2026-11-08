<?php

namespace RonasIT\ProjectInitializator\Tests;

use Illuminate\Support\ServiceProvider;
use RonasIT\ProjectInitializator\ProjectInitializatorServiceProvider;

class ProjectInitializatorServiceProviderTest extends TestCase
{
    public function testPublishableClassStubs(): void
    {
        $this->assertPublishedStub('initializator-role-enum', app_path('Enums/User/RoleEnum.php'), 'role_enum.php');
        $this->assertPublishedStub('initializator-clerk-user-repository', app_path('Support/Clerk/ClerkUserRepository.php'), 'clerk_user_repository.php');
    }

    protected function assertPublishedStub(string $tag, string $destination, string $fixture): void
    {
        $paths = ServiceProvider::pathsToPublish(ProjectInitializatorServiceProvider::class, $tag);

        $this->assertEquals([$destination], array_values($paths));
        $this->assertEquals($this->getFixture($fixture), file_get_contents(array_key_first($paths)));
    }
}
