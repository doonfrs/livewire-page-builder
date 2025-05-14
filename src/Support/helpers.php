<?php

use Trinavo\LivewirePageBuilder\Services\LocalizationService;

if (!function_exists('pb_localization_service')) {
    /**
     * Get the localization service instance
     * 
     * @return \Trinavo\LivewirePageBuilder\Services\LocalizationService
     */
    function pb_localization_service(): LocalizationService
    {
        return app(LocalizationService::class);
    }
}

if (!function_exists('pb_localize_content')) {
    /**
     * Get localized value from a multilingual content structure
     * 
     * @param mixed $content The content which may be a multilingual structure
     * @param string|null $locale The locale to get content for, defaults to current app locale
     * @return mixed The localized content or original content if not multilingual
     */
    function pb_localize_content($content, ?string $locale = null)
    {
        return pb_localization_service()->getLocalizedValue($content, $locale);
    }
}

if (!function_exists('pb_content_locales')) {
    /**
     * Get the available content locales
     * 
     * @return array
     */
    function pb_content_locales(): array
    {
        return pb_localization_service()->getContentLocales();
    }
}

if (!function_exists('pb_default_content_locale')) {
    /**
     * Get the default content locale
     * 
     * @return string
     */
    function pb_default_content_locale(): string
    {
        return pb_localization_service()->getDefaultContentLocale();
    }
}

if (!function_exists('pb_create_multilingual_content')) {
    /**
     * Create a multilingual content structure
     * 
     * @param array $values Associative array of locale => value pairs
     * @param string|null $defaultLocale The default locale
     * @return array The multilingual content structure
     */
    function pb_create_multilingual_content(array $values, ?string $defaultLocale = null): array
    {
        return pb_localization_service()->createMultilingualContent($values, $defaultLocale);
    }
}
