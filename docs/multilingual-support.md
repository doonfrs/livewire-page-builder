# Multilingual Support

The Page Builder offers comprehensive multilingual support for both the user interface (UI) and content. This means you can:

1. **Localize the UI**: Change the language of buttons, menus, and all interface elements
2. **Create multilingual content**: Author and manage content in multiple languages using RichText properties

## Configuration

Multilingual settings are configured in `config/page-builder.php`:

```php
'localization' => [
    // UI locales affect the builder interface (buttons, labels, etc.)
    'ui_locales' => [
        'en' => 'English',
        'ar' => 'العربية',
        'fr' => 'Français',
        // Add more languages as needed
    ],

    // Content locales are used for multilingual content in the builder
    'content_locales' => [
        'en' => 'English',
        'ar' => 'العربية',
        'fr' => 'Français',
        // Add more languages as needed
    ],

    // Default locale for new content
    'default_content_locale' => 'en',
],
```

## UI Localization

The builder interface can be displayed in multiple languages:

1. **Language Switcher**: A language switcher appears in the builder toolbar for users to change the interface language
2. **Session-Based**: The selected UI language is stored in the session and persists between page loads
3. **Automatic Translation**: The package automatically loads the appropriate translation files

### Adding UI Languages

1. Publish the translation files:

   ```bash
   php artisan vendor:publish --tag=page-builder-translations
   ```

2. Create new language files in the `lang/vendor/page-builder` directory or add to the config file

## Content Localization

RichText content can be authored in multiple languages:

1. **Language Tabs**: Rich text fields show language tabs when multilingual mode is enabled
2. **Independent Content**: Each language has independent content that can be edited separately
3. **Default Fallbacks**: If content isn't available in the selected language, it falls back to the default language

### RichText Multilingual Support

The `RichTextProperty` type is the only property type that supports multilingual content:

```php
public function getPageBuilderProperties(): array
{
    return [
        TextProperty::make('title')
            ->label('Title'),
            
        RichTextProperty::make('content', 'Main Content')
            ->multilingual(true), // Enable multilingual (true is default)
    ];
}
```

You can disable multilingual mode if needed:

```php
RichTextProperty::make('simple_content', 'Simple Content')
    ->multilingual(false) // Disable multilingual mode
```

## Programmatic Control

You can programmatically control localization through the `LocalizationService`:

```php
use Trinavo\LivewirePageBuilder\Services\LocalizationService;

// Get the service
$localizationService = app(LocalizationService::class);

// Change UI locales dynamically
$localizationService->setUiLocales([
    'en' => 'English',
    'fr' => 'French',
    'es' => 'Spanish',
]);

// Change content locales dynamically
$localizationService->setContentLocales([
    'en' => 'English',
    'fr' => 'French',
    'es' => 'Spanish',
]);

// Set the default content locale
$localizationService->setDefaultContentLocale('en');

// Switch the UI locale at runtime
$localizationService->setUiLocale('fr');

// Add individual locales
$localizationService->addUiLocale('de', 'German');
$localizationService->addContentLocale('de', 'German');

// Remove locales
$localizationService->removeUiLocale('de');
$localizationService->removeContentLocale('de');
```

## Accessing Multilingual Content

When rendering pages, you can access multilingual content using the `LocalizationService`:

```php
use Trinavo\LivewirePageBuilder\Services\LocalizationService;

// Get localized value from multilingual content
$localizationService = app(LocalizationService::class);

// Get content in current locale (or fallback)
$content = $localizationService->getLocalizedValue($block->properties['content']);

// Get content in specific locale
$frenchContent = $localizationService->getLocalizedValue($block->properties['content'], 'fr');
```

## Best Practices

1. **Consistent Locales**: Keep UI and content locales consistent for better user experience
2. **Validate Translations**: Ensure all required content is translated in all languages
3. **Set Default Fallbacks**: Always configure a default locale for fallback content
4. **RTL Support**: For languages like Arabic, ensure your CSS supports RTL layouts with Tailwind's `rtl:` variant

## Adding New Translations

To add translations for UI elements:

1. Create JSON files in `lang/vendor/page-builder/{locale}.json`
2. Add translations in key-value format:

```json
{
    "Add Row": "Ajouter une rangée",
    "Pages": "Pages",
    "Save Page": "Enregistrer la page",
    "Add Block": "Ajouter un bloc"
}
```

For a complete set of translatable strings, check the English version in the package's `lang/en.json` file.
