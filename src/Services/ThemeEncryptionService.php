<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Trinavo\LivewirePageBuilder\Exceptions\ThemeEncryptionException;

class ThemeEncryptionService
{
    /**
     * Static cache for encryption settings
     */
    protected static array $cache = [
        'enabled' => false,
        'key' => '',
        'file_extension' => '.encrypted',
        'require_password' => true,
    ];

    /**
     * Create a new theme encryption service instance
     */
    public function __construct()
    {
        // Only load from config if cache is empty
        if (empty(static::$cache['key'])) {
            $this->loadFromConfig();
        }
    }

    /**
     * Load encryption settings from config
     */
    public function loadFromConfig(): self
    {
        static::$cache['enabled'] = Config::get('page-builder.encryption.enabled', false);
        static::$cache['key'] = Config::get('page-builder.encryption.key', '');
        static::$cache['file_extension'] = Config::get('page-builder.encryption.file_extension', '.encrypted');
        static::$cache['require_password'] = Config::get('page-builder.encryption.require_password', true);

        return $this;
    }

    /**
     * Force reload from config and flush cache
     */
    public function flushCache(): self
    {
        static::$cache = [
            'enabled' => false,
            'key' => '',
            'file_extension' => '.encrypted',
            'require_password' => true,
        ];

        return $this->loadFromConfig();
    }

    /**
     * Set encryption enabled status dynamically
     */
    public function setEncryptionEnabled(bool $enabled): self
    {
        static::$cache['enabled'] = $enabled;

        return $this;
    }

    /**
     * Set encryption key dynamically
     */
    public function setEncryptionKey(string $key): self
    {
        static::$cache['key'] = $key;

        return $this;
    }

    /**
     * Set file extension for encrypted themes
     */
    public function setFileExtension(string $extension): self
    {
        static::$cache['file_extension'] = $extension;

        return $this;
    }

    /**
     * Set whether password is required for encrypted themes
     */
    public function setRequirePassword(bool $require): self
    {
        static::$cache['require_password'] = $require;

        return $this;
    }

    /**
     * Get encryption enabled status
     */
    public function isEncryptionEnabled(): bool
    {
        return static::$cache['enabled'] && ! empty(static::$cache['key']);
    }

    /**
     * Check if encryption is properly configured
     */
    public function isEncryptionConfigured(): bool
    {
        return static::$cache['enabled'] && ! empty(static::$cache['key']);
    }

    /**
     * Get the current encryption key
     */
    public function getEncryptionKey(): ?string
    {
        return static::$cache['key'] ?: null;
    }

    /**
     * Get file extension for encrypted themes
     */
    public function getFileExtension(): string
    {
        return static::$cache['file_extension'];
    }

    /**
     * Check if password is required for encrypted themes
     */
    public function isPasswordRequired(): bool
    {
        return static::$cache['require_password'];
    }

    /**
     * Encrypt theme data
     */
    public function encryptThemeData(array $themeData, ?string $password = null): ?string
    {
        if (! $this->isEncryptionEnabled()) {
            Log::warning('Theme encryption attempted but not enabled');

            return null;
        }

        try {
            // Use provided password or fallback to configured key
            $encryptionKey = $password ?: static::$cache['key'];

            if (empty($encryptionKey)) {
                throw new \Exception('No encryption key provided');
            }

            // Create encrypted theme structure (without revealing algorithm details)
            $encryptedTheme = [
                'encrypted' => true,
                'version' => '1.0',
                'encrypted_at' => now()->toISOString(),
                'data' => $this->encryptData($themeData, $encryptionKey),
            ];

            return json_encode($encryptedTheme, JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            report($e);

            return null;
        }
    }

    /**
     * Decrypt theme data
     */
    public function decryptThemeData(string $encryptedData, ?string $password = null): ?array
    {
        // Parse encrypted theme structure
        $encryptedTheme = json_decode($encryptedData, true);

        if (! $encryptedTheme || ! isset($encryptedTheme['encrypted']) || ! $encryptedTheme['encrypted']) {
            throw new ThemeEncryptionException(__('This file appears to be encrypted but has an invalid format. It may be corrupted or created with a different version.'));
        }

        // Use provided password or fallback to configured key
        $encryptionKey = $password ?: static::$cache['key'];

        if (empty($encryptionKey)) {
            throw new ThemeEncryptionException(__('This file is encrypted but no encryption key is configured. Please contact your administrator to set up the encryption key.'));
        }

        // Decrypt using the configured algorithm
        $decryptedData = $this->decryptData($encryptedTheme['data'], $encryptionKey);

        if (! $decryptedData) {
            throw new ThemeEncryptionException(__('This file is encrypted but cannot be decrypted. The encryption key may be incorrect or the file may be corrupted.'));
        }

        return $decryptedData;
    }

    /**
     * Check if data is encrypted
     */
    public function isEncrypted(string $data): bool
    {
        $parsed = json_decode($data, true);

        return $parsed && isset($parsed['encrypted']) && $parsed['encrypted'] === true;
    }

    /**
     * Encrypt a payload with AES-256-GCM. Output is base64(iv || tag || ciphertext).
     * The 16-byte GCM tag authenticates the ciphertext + IV so any tampering is
     * detected on decryption.
     */
    protected function encryptData(array $data, string $key): string
    {
        $jsonData = json_encode($data);
        $iv = random_bytes(12);
        $tag = '';

        $encrypted = openssl_encrypt($jsonData, 'AES-256-GCM', $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($encrypted === false) {
            throw new \Exception('Encryption failed');
        }

        return base64_encode($iv.$tag.$encrypted);
    }

    /**
     * Decrypt an AES-256-GCM payload produced by encryptData(). Returns null
     * if the ciphertext, IV, or tag is corrupted or the key is wrong.
     */
    protected function decryptData(string $encryptedData, string $key): ?array
    {
        try {
            $data = base64_decode($encryptedData);
            $iv = substr($data, 0, 12);
            $tag = substr($data, 12, 16);
            $ciphertext = substr($data, 28);

            $decrypted = openssl_decrypt($ciphertext, 'AES-256-GCM', $key, OPENSSL_RAW_DATA, $iv, $tag);

            if ($decrypted === false) {
                return null;
            }

            return json_decode($decrypted, true);

        } catch (\Exception $e) {
            report($e);

            return null;
        }
    }

    /**
     * Generate a secure encryption key
     */
    public function generateEncryptionKey(): string
    {
        return base64_encode(random_bytes(32));
    }

    /**
     * Validate encryption key format
     */
    public function validateEncryptionKey(string $key): bool
    {

        $decoded = base64_decode($key, true);

        return $decoded !== false && strlen($decoded) === 32;

    }
}
