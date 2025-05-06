<?php

namespace Trinavo\LivewirePageBuilder\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Console\InstallPageBuilderCommand;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;
use Trinavo\LivewirePageBuilder\Http\Livewire\BuilderBlock;
use Trinavo\LivewirePageBuilder\Http\Livewire\BuilderPageBlock;
use Trinavo\LivewirePageBuilder\Http\Livewire\ColorPickerComponent;
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

        $this->publishes([
            __DIR__.'/../../config/page-builder.php' => config_path('page-builder.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../../config/page-builder.php', 'page-builder');

        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/page-builder'),
        ], 'page-builder-views');

        Livewire::component('page-editor', PageEditor::class);
        Livewire::component('builder-block', BuilderBlock::class);
        Livewire::component('block-properties', BlockProperties::class);
        Livewire::component('row-block', RowBlock::class);
        Livewire::component('builder-page-block', BuilderPageBlock::class);
        Livewire::component('color-picker', ColorPickerComponent::class);

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
