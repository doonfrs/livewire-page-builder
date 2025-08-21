<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class ThemeEncryptionService
{
    /**
     * Static cache for encryption settings
     */
    protected static array $cache = [
        'enabled' => false,
        'key' => '',
        'algorithm' => 'AES-256-CBC',
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
        static::$cache['algorithm'] = Config::get('page-builder.encryption.algorithm', 'AES-256-CBC');
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
            'algorithm' => 'AES-256-CBC',
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
     * Set encryption algorithm dynamically
     */
    public function setEncryptionAlgorithm(string $algorithm): self
    {
        static::$cache['algorithm'] = $algorithm;

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
        return static::$cache['enabled'] &&
               ! empty(static::$cache['key']) &&
               ! empty(static::$cache['algorithm']);
    }

    /**
     * Get the current encryption key
     */
    public function getEncryptionKey(): ?string
    {
        return static::$cache['key'] ?: null;
    }

    /**
     * Get current encryption algorithm
     */
    public function getEncryptionAlgorithm(): string
    {
        return static::$cache['algorithm'];
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
        try {
            // Parse encrypted theme structure
            $encryptedTheme = json_decode($encryptedData, true);

            if (! $encryptedTheme || ! isset($encryptedTheme['encrypted']) || ! $encryptedTheme['encrypted']) {
                throw new \Exception('Invalid encrypted theme format. The file does not appear to be a properly encrypted theme.');
            }

            // Use provided password or fallback to configured key
            $encryptionKey = $password ?: static::$cache['key'];

            if (empty($encryptionKey)) {
                throw new \Exception('No encryption key provided. Please configure the encryption key in your settings or provide a password.');
            }

            // Decrypt using the configured algorithm
            $decryptedData = $this->decryptData($encryptedTheme['data'], $encryptionKey);

            if (! $decryptedData) {
                throw new \Exception('Failed to decrypt theme data. The encryption key may be incorrect or the file may be corrupted.');
            }

            return $decryptedData;

        } catch (\Exception $e) {
            report($e);

            return null;
        }
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
     * Encrypt data using the configured algorithm
     */
    protected function encryptData(array $data, string $key): string
    {
        $jsonData = json_encode($data);

        if (static::$cache['algorithm'] === 'AES-256-GCM') {
            return $this->encryptAES256GCM($jsonData, $key);
        } else {
            return $this->encryptAES256CBC($jsonData, $key);
        }
    }

    /**
     * Decrypt data using the configured algorithm
     */
    protected function decryptData(string $encryptedData, string $key): ?array
    {
        try {
            if (static::$cache['algorithm'] === 'AES-256-GCM') {
                $decrypted = $this->decryptAES256GCM($encryptedData, $key);
            } else {
                $decrypted = $this->decryptAES256CBC($encryptedData, $key);
            }

            if (! $decrypted) {
                return null;
            }

            return json_decode($decrypted, true);

        } catch (\Exception $e) {
            report($e);

            return null;
        }
    }

    /**
     * Encrypt using AES-256-CBC
     */
    protected function encryptAES256CBC(string $data, string $key): string
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new \Exception('Encryption failed');
        }

        return base64_encode($iv.$encrypted);
    }

    /**
     * Decrypt using AES-256-CBC
     */
    protected function decryptAES256CBC(string $encryptedData, string $key): ?string
    {
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        return $decrypted !== false ? $decrypted : null;
    }

    /**
     * Encrypt using AES-256-GCM
     */
    protected function encryptAES256GCM(string $data, string $key): string
    {
        $iv = random_bytes(12);
        $tag = '';

        $encrypted = openssl_encrypt($data, 'AES-256-GCM', $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($encrypted === false) {
            throw new \Exception('Encryption failed');
        }

        return base64_encode($iv.$tag.$encrypted);
    }

    /**
     * Decrypt using AES-256-GCM
     */
    protected function decryptAES256GCM(string $encryptedData, string $key): ?string
    {
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 12);
        $tag = substr($data, 12, 16);
        $encrypted = substr($data, 28);

        $decrypted = openssl_decrypt($encrypted, 'AES-256-GCM', $key, OPENSSL_RAW_DATA, $iv, $tag);

        return $decrypted !== false ? $decrypted : null;
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
