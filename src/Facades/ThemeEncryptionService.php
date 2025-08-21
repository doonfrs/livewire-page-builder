<?php

namespace Trinavo\LivewirePageBuilder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static self loadFromConfig()
 * @method static self flushCache()
 * @method static self setEncryptionEnabled(bool $enabled)
 * @method static self setEncryptionKey(string $key)
 * @method static self setEncryptionAlgorithm(string $algorithm)
 * @method static self setFileExtension(string $extension)
 * @method static self setRequirePassword(bool $require)
 * @method static bool isEncryptionEnabled()
 * @method static string getEncryptionKey()
 * @method static string getEncryptionAlgorithm()
 * @method static string getFileExtension()
 * @method static bool isPasswordRequired()
 * @method static string|null encryptThemeData(array $themeData, string|null $password = null)
 * @method static array|null decryptThemeData(string $encryptedData, string|null $password = null)
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
