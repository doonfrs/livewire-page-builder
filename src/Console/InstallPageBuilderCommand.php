<?php

namespace Trinavo\LivewirePageBuilder\Console;

use Illuminate\Console\Command;

class InstallPageBuilderCommand extends Command
{
    protected $signature = 'pagebuilder:install';
    protected $description = 'Install Livewire Page Builder Tailwind source path';

    public function handle()
    {
        $cssPath = base_path('resources/css/app.css');
        $sourceLine = "@source '../../vendor/trinavo/livewire-page-builder/resources/**/*.blade.php';\n";

        if (file_exists($cssPath)) {
            $css = file_get_contents($cssPath);
            if (strpos($css, $sourceLine) === false) {
                file_put_contents($cssPath, $css . $sourceLine);
                $this->info('Source line added to app.css!');
            } else {
                $this->info('Source line already exists in app.css.');
            }
        } else {
            $this->error('app.css not found!');
        }
    }
} 