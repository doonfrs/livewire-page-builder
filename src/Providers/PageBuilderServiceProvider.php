<?php

namespace Trinavo\LivewirePageBuilder\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\Block;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;
use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Http\Livewire\Row;
use Trinavo\LivewirePageBuilder\Http\Livewire\BuilderBlock;
use Trinavo\LivewirePageBuilder\Console\InstallPageBuilderCommand;
use Illuminate\Support\Str;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;

class PageBuilderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../../Database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'page-builder');

        $this->publishes([
            __DIR__ . '/../../config/page-builder.php' => config_path('page-builder.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../../config/page-builder.php', 'page-builder');

        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/page-builder'),
        ], 'page-builder-views');

        Livewire::component('page-editor', PageEditor::class);
        Livewire::component('block', BuilderBlock::class);
        Livewire::component('block-properties', BlockProperties::class);
        Livewire::component('row', Row::class);

        $this->registerBlocks();
    }

    public function register(): void
    {
        // Bindings or services can be registered here if needed.
        $this->commands([
            InstallPageBuilderCommand::class,
        ]);
    }

    private function registerBlocks()
    {
        // Register user blocks with kebab-case aliases
        foreach (config('page-builder.blocks', []) as $blockClass) {
            if (class_exists($blockClass)) {
                $alias = app(PageBuilderService::class)->getClassAlias($blockClass);
                Livewire::component($alias, $blockClass);
            }
        }
    }
}
