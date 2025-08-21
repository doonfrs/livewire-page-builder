<?php

namespace Trinavo\LivewirePageBuilder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Trinavo\LivewirePageBuilder\Models\Theme|null importTheme(array $data, bool $overwriteExisting = false)
 * @method static \Trinavo\LivewirePageBuilder\Models\Theme|null importThemeFromFile(string $filePath, bool $overwriteExisting = false)
 * @method static \Trinavo\LivewirePageBuilder\Models\Theme|null importEncryptedTheme(string $encryptedData, bool $overwriteExisting = false, string|null $password = null)
 * @method static \Trinavo\LivewirePageBuilder\Models\Theme|null importThemeFromEncryptedFile(string $filePath, bool $overwriteExisting = false, string|null $password = null)
 * @method static array|null exportTheme(int|Theme $theme)
 * @method static string|null exportThemeToFile(int|Theme $theme, string|null $directory = null)
 * @method static string|null exportThemeToEncryptedFile(int|Theme $theme, string|null $directory = null, string|null $password = null)
 * @method static string|null exportThemeAsJson(int|Theme $theme, int $jsonFlags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
 * @method static string|null exportThemeAsEncryptedJson(int|Theme $theme, string|null $password = null)
 * @method static \Trinavo\LivewirePageBuilder\Models\Theme|null cloneTheme(int|Theme $theme, string $newName)
 * @method static bool isEncryptionEnabled()
 * @method static \Trinavo\LivewirePageBuilder\Services\ThemeEncryptionService getEncryptionService()
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
