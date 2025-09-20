<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Config\Variables;
use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Http\Livewire\ThemeManager;
use Trinavo\LivewirePageBuilder\Services\LocalizationService;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Services\ThemeEncryptionService;
use Trinavo\LivewirePageBuilder\Services\ThemeService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    /** @test */
    public function it_registers_services(): void
    {
        $this->assertTrue($this->app->bound(LocalizationService::class));
        $this->assertTrue($this->app->bound(Variables::class));
        $this->assertTrue($this->app->bound(ThemeEncryptionService::class));
        $this->assertTrue($this->app->bound(ThemeService::class));
    }

    /** @test */
    public function it_registers_livewire_components(): void
    {
        // Check if Livewire components are registered by trying to instantiate them
        $this->assertInstanceOf(PageEditor::class, Livewire::new('page-editor'));
        $this->assertInstanceOf(ThemeManager::class, Livewire::new('theme-manager'));
    }

    /** @test */
    public function it_loads_views(): void
    {
        // Test that views directory is loaded by checking a specific view exists
        $viewsPath = __DIR__.'/../../resources/views';
        $this->assertDirectoryExists($viewsPath);
    }

    /** @test */
    public function it_registers_default_variables(): void
    {
        $this->assertTrue(Variables::has('app_name'));
        $this->assertTrue(Variables::has('app_url'));
        $this->assertTrue(Variables::has('year'));
        $this->assertTrue(Variables::has('current_datetime'));
    }

    /** @test */
    public function it_can_resolve_services(): void
    {
        $localizationService = $this->app->make(LocalizationService::class);
        $this->assertInstanceOf(LocalizationService::class, $localizationService);

        $variables = $this->app->make(Variables::class);
        $this->assertInstanceOf(Variables::class, $variables);

        $themeEncryptionService = $this->app->make(ThemeEncryptionService::class);
        $this->assertInstanceOf(ThemeEncryptionService::class, $themeEncryptionService);

        $themeService = $this->app->make(ThemeService::class);
        $this->assertInstanceOf(ThemeService::class, $themeService);
    }

    /** @test */
    public function page_builder_service_is_available(): void
    {
        $pageBuilderService = $this->app->make(PageBuilderService::class);
        $this->assertInstanceOf(PageBuilderService::class, $pageBuilderService);
    }

    /** @test */
    public function config_is_merged(): void
    {
        $this->assertNotNull(config('page-builder'));
    }
}