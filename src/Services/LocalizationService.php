<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Arr;

class LocalizationService
{
    /**
     * Static cache for values to ensure consistency across instances
     */
    protected static array $cache = [
        'ui_locales' => [],
        'content_locales' => [],
        'default_content_locale' => 'en',
    ];

    /**
     * Create a new localization service instance
     */
    public function __construct()
    {
        // Only load from config if cache is empty
        if (empty(static::$cache['ui_locales']) && empty(static::$cache['content_locales'])) {
            $this->loadFromConfig();
        }
    }

    /**
     * Load localization settings from config
     */
    public function loadFromConfig(): self
    {
        static::$cache['ui_locales'] = Config::get('page-builder.localization.ui_locales', ['en' => 'English']);
        static::$cache['content_locales'] = Config::get('page-builder.localization.content_locales', ['en' => 'English']);
        static::$cache['default_content_locale'] = Config::get('page-builder.localization.default_content_locale', 'en');

        return $this;
    }

    /**
     * Force reload from config and flush cache
     */
    public function flushCache(): self
    {
        static::$cache = [
            'ui_locales' => [],
            'content_locales' => [],
            'default_content_locale' => 'en',
        ];

        return $this->loadFromConfig();
    }

    /**
     * Set UI locales dynamically
     */
    public function setUiLocales(array $locales): self
    {
        static::$cache['ui_locales'] = $locales;
        return $this;
    }

    /**
     * Set content locales dynamically
     */
    public function setContentLocales(array $locales): self
    {
        static::$cache['content_locales'] = $locales;
        return $this;
    }

    /**
     * Set default content locale
     */
    public function setDefaultContentLocale(string $locale): self
    {
        static::$cache['default_content_locale'] = $locale;
        return $this;
    }

    /**
     * Add a UI locale
     */
    public function addUiLocale(string $code, string $name): self
    {
        static::$cache['ui_locales'][$code] = $name;
        return $this;
    }

    /**
     * Add a content locale
     */
    public function addContentLocale(string $code, string $name): self
    {
        static::$cache['content_locales'][$code] = $name;
        return $this;
    }

    /**
     * Remove a UI locale
     */
    public function removeUiLocale(string $code): self
    {
        unset(static::$cache['ui_locales'][$code]);
        return $this;
    }

    /**
     * Remove a content locale
     */
    public function removeContentLocale(string $code): self
    {
        unset(static::$cache['content_locales'][$code]);
        return $this;
    }

    /**
     * Get UI locales
     */
    public function getUiLocales(): array
    {
        return static::$cache['ui_locales'];
    }

    /**
     * Get content locales
     */
    public function getContentLocales(): array
    {
        return static::$cache['content_locales'];
    }

    /**
     * Get default content locale
     */
    public function getDefaultContentLocale(): string
    {
        return static::$cache['default_content_locale'];
    }

    /**
     * Register JSON translations for all UI locales
     */
    public function registerJsonTranslations(string $path): void
    {
        $locales = array_keys($this->getUiLocales());

        foreach ($locales as $locale) {
            App::make('translator')->addJsonPath($path, $locale);
        }
    }

    /**
     * Share localization data with views
     */
    public function shareWithViews(): void
    {
        view()->share('pageBuilderContentLocales', $this->getContentLocales());
        view()->share('pageBuilderDefaultContentLocale', $this->getDefaultContentLocale());
        view()->share('pageBuilderUiLocales', $this->getUiLocales());
    }

    /**
     * Get localized value from a multilingual content structure
     * 
     * @param mixed $content The content which may be a multilingual structure
     * @param string|null $locale The locale to get content for, defaults to current app locale
     * @return mixed The localized content or original content if not multilingual
     */
    public function getLocalizedValue($content, ?string $locale = null)
    {
        // If not an array or not multilingual, return as is
        if (!is_array($content) || !isset($content['multilingual']) || $content['multilingual'] !== true) {
            return $content;
        }

        // Use provided locale or fallback to current app locale
        $locale = $locale ?? app()->getLocale();
        $defaultLocale = $content['default_locale'] ?? $this->getDefaultContentLocale();

        // Get content for requested locale or fall back to default locale
        if (isset($content['values'][$locale])) {
            return $content['values'][$locale];
        } elseif (isset($content['values'][$defaultLocale])) {
            return $content['values'][$defaultLocale];
        } else {
            // Fallback to first available locale
            return !empty($content['values']) ? reset($content['values']) : '';
        }
    }

    /**
     * Create a multilingual content structure
     * 
     * @param array $values Associative array of locale => value pairs
     * @param string|null $defaultLocale The default locale, defaults to service default
     * @return array The multilingual content structure
     */
    public function createMultilingualContent(array $values, ?string $defaultLocale = null): array
    {
        return [
            'multilingual' => true,
            'values' => $values,
            'default_locale' => $defaultLocale ?? $this->getDefaultContentLocale()
        ];
    }
}
