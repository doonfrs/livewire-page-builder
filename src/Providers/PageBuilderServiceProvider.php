<?php

namespace Trinavo\LivewirePageBuilder\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Console\InstallPageBuilderCommand;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties\ColorPicker;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties\ImageProperty;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties\SelectProperty;
use Trinavo\LivewirePageBuilder\Http\Livewire\BuilderBlock;
use Trinavo\LivewirePageBuilder\Http\Livewire\BuilderPageBlock;
use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;

class PageBuilderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'page-builder');
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'page-builder');

        // Also register the lang directory for JSON translations
        $this->loadJsonTranslationsFrom(__DIR__.'/../../lang', 'page-builder');

        // Register blade components with namespace
        $this->loadViewsFrom(__DIR__.'/../../resources/views/components', 'page-builder');

        // Register anonymous blade components
        $this->loadViewComponentsAs('page-builder', []);
        Blade::componentNamespace('Trinavo\\LivewirePageBuilder\\View\\Components', 'page-builder');

        $this->publishes([
            __DIR__.'/../../config/page-builder.php' => config_path('page-builder.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../../config/page-builder.php', 'page-builder');

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

        Livewire::component('page-editor', PageEditor::class);
        Livewire::component('builder-block', BuilderBlock::class);
        Livewire::component('block-properties', BlockProperties::class);
        Livewire::component('row-block', RowBlock::class);
        Livewire::component('builder-page-block', BuilderPageBlock::class);
        Livewire::component('block-properties.color-picker', ColorPicker::class);
        Livewire::component('block-properties.image-property', ImageProperty::class);
        Livewire::component('block-properties.select-property', SelectProperty::class);

        app(PageBuilderService::class)->registerBlocks();
    }

    public function register(): void
    {
        // Bindings or services can be registered here if needed.
        $this->commands([
            InstallPageBuilderCommand::class,
        ]);
    }
}
