<?php

namespace Trinavo\LivewirePageBuilder\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Trinavo\LivewirePageBuilder\Providers\PageBuilderServiceProvider;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            PageBuilderServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        // No longer need icon mocking since we use inline SVG

        // Configure page builder
        config()->set('page-builder.pages', [
            'test-page' => 'Test Page',
        ]);
    }

    protected function setUpDatabase(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function getPackageAliases($app): array
    {
        return [
            'PageBuilderVariables' => \Trinavo\LivewirePageBuilder\Facades\PageBuilderVariables::class,
            'ThemeEncryptionService' => \Trinavo\LivewirePageBuilder\Facades\ThemeEncryptionService::class,
        ];
    }
}
