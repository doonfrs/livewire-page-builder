<?php

namespace Trinavo\LivewirePageBuilder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Trinavo\LivewirePageBuilder\Models\Theme|null importTheme(array $data, bool $overwriteExisting = false)
 * @method static \Trinavo\LivewirePageBuilder\Models\Theme|null importThemeFromFile(string $filePath, bool $overwriteExisting = false)
 * @method static array|null exportTheme(int|Theme $theme)
 * @method static string|null exportThemeToFile(int|Theme $theme, ?string $directory = null)
 * @method static string|null exportThemeAsJson(int|Theme $theme, int $jsonFlags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
 * @method static \Trinavo\LivewirePageBuilder\Models\Theme|null cloneTheme(int|Theme $theme, string $newName)
 *
 * @see \Trinavo\LivewirePageBuilder\Services\ThemeService
 */
class ThemeService extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'livewire-page-builder.theme-service';
    }
}
