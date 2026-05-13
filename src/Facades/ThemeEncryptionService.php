<?php

namespace Trinavo\LivewirePageBuilder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * ThemeEncryptionService Facade
 *
 * @method static bool isEncryptionEnabled()
 * @method static bool isEncryptionConfigured()
 * @method static string getEncryptionKey()
 * @method static string getFileExtension()
 * @method static bool getRequirePassword()
 * @method static void setEncryptionEnabled(bool $enabled)
 * @method static void setEncryptionKey(string $key)
 * @method static void setFileExtension(string $extension)
 * @method static void setRequirePassword(bool $require)
 * @method static void flushConfig()
 * @method static void loadConfig()
 * @method static string|null encryptThemeData(array $themeData, ?string $password = null)
 * @method static array|null decryptThemeData(string $encryptedData, ?string $password = null)
 * @method static bool isEncrypted(string $data)
 * @method static string generateEncryptionKey()
 * @method static bool validateEncryptionKey(string $key)
 *
 * @see \Trinavo\LivewirePageBuilder\Services\ThemeEncryptionService
 */
class ThemeEncryptionService extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'livewire-page-builder.theme-encryption-service';
    }
}
