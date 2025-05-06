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

        // 4. Publish built assets to the correct public path
        $this->publishAssets();

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

    protected function publishAssets()
    {
        // Check if public/build directory exists in the package
        $sourcePath = __DIR__.'/../../public/build';

        if (! is_dir($sourcePath)) {
            if (! $this->option('silent')) {
                $this->warn('× Build directory not found!');
            }

            // In silent mode, automatically run build without confirmation
            $shouldBuild = $this->option('silent') ? true :
                $this->confirm('Would you like to run the build process now?', true);

            if ($shouldBuild) {
                if (! $this->option('silent')) {
                    $this->info('Running npm run build...');
                }

                // Get the package root directory
                $packagePath = realpath(__DIR__.'/../../');

                // Execute npm run build in the package directory
                $process = proc_open('cd '.$packagePath.' && npm run build',
                    [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                    $pipes
                );

                if (is_resource($process)) {
                    $output = stream_get_contents($pipes[1]);
                    $error = stream_get_contents($pipes[2]);
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    proc_close($process);

                    if (! empty($error) && ! $this->option('silent')) {
                        $this->error('Build process failed:');
                        $this->line($error);

                        return;
                    }

                    if (! $this->option('silent')) {
                        $this->info('Build completed successfully!');
                    }
                } elseif (! $this->option('silent')) {
                    $this->error('Failed to start build process. Please run npm run build manually.');

                    return;
                }
            } elseif (! $this->option('silent')) {
                $this->warn('Skipping asset publishing due to missing build files.');

                return;
            }
        }

        // Use the Laravel vendor:publish command to publish assets
        $params = ['--provider' => 'Trinavo\\LivewirePageBuilder\\Providers\\PageBuilderServiceProvider'];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        if ($this->option('silent')) {
            $params['--quiet'] = true;
        }

        $this->call('vendor:publish', array_merge($params, ['--tag' => 'page-builder-assets']));

        // Check if the assets were successfully published
        if (! is_dir(public_path('vendor/page-builder/build')) && ! $this->option('silent')) {
            $this->warn('× Assets not published. The build files may not exist in the package.');
            $this->info('Please ensure you have built the assets with "npm run build" before publishing the package.');
        } elseif (! $this->option('silent')) {
            $this->info('✓ Assets published successfully');
        }
    }
}
