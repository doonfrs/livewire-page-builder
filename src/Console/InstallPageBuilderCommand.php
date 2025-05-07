<?php

namespace Trinavo\LivewirePageBuilder\Console;

use Illuminate\Console\Command;

class InstallPageBuilderCommand extends Command
{
    protected $signature = 'pagebuilder:install 
                           {--force : Force overwrite of existing files}
                           {--silent : Run in silent mode with default options}';

    protected $description = 'Install Livewire Page Builder assets and configurations';

    public function handle()
    {
        if (! $this->option('silent')) {
            $this->info('Installing Livewire Page Builder...');
        }

        // 1. Add tailwind source path
        $this->addTailwindSourcePath();

        // 2. Publish config
        $this->publishConfig();

        // 3. Publish views (only if --force or not silent mode and confirmed)
        if ($this->option('force') ||
            (! $this->option('silent') && $this->confirm('Would you like to publish the views?', false))) {
            $this->publishViews();
        }

        // 5. Run migrations (default in silent mode)
        if ($this->option('silent') || $this->confirm('Would you like to run migrations?', true)) {
            $this->call('migrate', $this->option('silent') ? ['--quiet' => true] : []);
        }

        if (! $this->option('silent')) {
            $this->info('Livewire Page Builder has been installed successfully!');
            $this->info('To access the page builder, visit: /page-builder');
        }
    }

    protected function addTailwindSourcePath()
    {
        $cssPath = base_path('resources/css/app.css');
        $sourceLine = "@source '../../vendor/trinavo/livewire-page-builder/resources/**/*.blade.php';\n";

        if (file_exists($cssPath)) {
            $css = file_get_contents($cssPath);
            if (strpos($css, $sourceLine) === false) {
                file_put_contents($cssPath, $css.$sourceLine);
                if (! $this->option('silent')) {
                    $this->info('✓ Source line added to app.css');
                }
            } elseif (! $this->option('silent')) {
                $this->info('✓ Source line already exists in app.css');
            }
        } elseif (! $this->option('silent')) {
            $this->error('× app.css not found! Make sure your application is using Tailwind CSS.');
        }
    }

    protected function publishConfig()
    {
        $params = ['--provider' => 'Trinavo\\LivewirePageBuilder\\Providers\\PageBuilderServiceProvider'];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        if ($this->option('silent')) {
            $params['--quiet'] = true;
        }

        $this->call('vendor:publish', array_merge($params, ['--tag' => 'config']));
    }

    protected function publishViews()
    {
        $params = ['--provider' => 'Trinavo\\LivewirePageBuilder\\Providers\\PageBuilderServiceProvider'];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        if ($this->option('silent')) {
            $params['--quiet'] = true;
        }

        $this->call('vendor:publish', array_merge($params, ['--tag' => 'page-builder-views']));
    }
}
