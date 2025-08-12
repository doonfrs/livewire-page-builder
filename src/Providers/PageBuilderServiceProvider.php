<?php

namespace Trinavo\LivewirePageBuilder\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Config\Variables;
use Trinavo\LivewirePageBuilder\Console\InstallPageBuilderCommand;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties\ColorPicker;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties\ImageProperty;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties\RichTextProperty;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties\SelectProperty;
use Trinavo\LivewirePageBuilder\Http\Livewire\BuilderBlock;
use Trinavo\LivewirePageBuilder\Http\Livewire\BuilderPageBlock;
use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
use Trinavo\LivewirePageBuilder\Http\Livewire\ThemeManager;
use Trinavo\LivewirePageBuilder\Services\LocalizationService;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;

class PageBuilderServiceProvider extends ServiceProvider
{
    protected LocalizationService $localizationService;

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'page-builder');

        // Load the configuration first
        $this->mergeConfigFrom(__DIR__.'/../../config/page-builder.php', 'page-builder');

        // Register middleware with the router
        if ($this->app->bound('router')) {
            $this->app->make('router')->aliasMiddleware(
                'page-builder-localization',
                \Trinavo\LivewirePageBuilder\Http\Middleware\LocalizationMiddleware::class
            );
        }

        // Get the localization service
        $this->localizationService = app(LocalizationService::class);

        // Set up localization using the service
        $this->setupLocalization();

        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'page-builder');

        // Register blade components with namespace
        $this->loadViewsFrom(__DIR__.'/../../resources/views/components', 'page-builder');

        // Register anonymous blade components
        $this->loadViewComponentsAs('page-builder', []);
        Blade::componentNamespace('Trinavo\\LivewirePageBuilder\\View\\Components', 'page-builder');

        $this->publishes([
            __DIR__.'/../../config/page-builder.php' => config_path('page-builder.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/page-builder'),
        ], 'page-builder-views');

        // Add asset publishing for Vite build files
        $this->publishes([
            __DIR__.'/../../public/build' => public_path('vendor/page-builder/build'),
        ], 'page-builder-assets');

        // Publish translation files
        $this->publishes([
            __DIR__.'/../../lang' => lang_path('vendor/page-builder'),
        ], 'page-builder-translations');

        // Add vite resources
        $this->publishes([
            __DIR__.'/../../resources/js' => resource_path('js/vendor/page-builder'),
        ], 'page-builder-js');

        // Register Livewire components
        $this->registerLivewireComponents();

        // Register blocks
        app(PageBuilderService::class)->registerBlocks();

        // Register default variables
        $this->registerDefaultVariables();
    }

    public function register(): void
    {
        // Register the localization service
        $this->app->singleton(LocalizationService::class, function ($app) {
            return new LocalizationService;
        });

        // Register the Variables class
        $this->app->singleton(Variables::class, function ($app) {
            return new Variables;
        });

        // Register middleware for handling language switching
        $this->app->singleton(\Trinavo\LivewirePageBuilder\Http\Middleware\LocalizationMiddleware::class);

        // Register commands
        $this->commands([
            InstallPageBuilderCommand::class,
        ]);
    }

    /**
     * Set up localization using the LocalizationService
     */
    protected function setupLocalization(): void
    {
        // Register JSON translations for all UI locales
        $this->localizationService->registerJsonTranslations(__DIR__.'/../../lang');

        // Share localization data with views
        $this->localizationService->shareWithViews();
    }

    /**
     * Get the localization service
     */
    public function getLocalizationService(): LocalizationService
    {
        return $this->localizationService;
    }

    /**
     * Register Livewire components
     */
    protected function registerLivewireComponents(): void
    {
        Livewire::component('page-editor', PageEditor::class);
        Livewire::component('theme-manager', ThemeManager::class);
        Livewire::component('builder-block', BuilderBlock::class);
        Livewire::component('block-properties', BlockProperties::class);
        Livewire::component('row-block', RowBlock::class);
        Livewire::component('builder-page-block', BuilderPageBlock::class);
        Livewire::component('block-properties.color-picker', ColorPicker::class);
        Livewire::component('block-properties.image-property', ImageProperty::class);
        Livewire::component('block-properties.select-property', SelectProperty::class);
        Livewire::component('block-properties.richtext-property', RichTextProperty::class);
        Livewire::component('language-switcher', \Trinavo\LivewirePageBuilder\Http\Livewire\LanguageSwitcher::class);
    }

    /**
     * Register default variables
     */
    protected function registerDefaultVariables(): void
    {
        // Register default variables
        Variables::registerMany([
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'year' => date('Y'),
            'current_datetime' => fn () => now()->format('Y-m-d H:i:s'),
        ]);

        // Register any variables defined in the config
        if (config('page-builder.variables')) {
            Variables::registerMany(config('page-builder.variables'));
        }
    }
}
