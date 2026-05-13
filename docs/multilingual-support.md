# Multilingual Support

The package separates **UI language** (what the editor's buttons and menus say) from **content language** (what your editors actually type). Both are configured independently, and either can be changed at runtime.

---

## Configuration

```php
// config/page-builder.php
'localization' => [
    'ui_locales' => [
        'en' => 'English',
        'ar' => 'العربية',
        'fr' => 'Français',
    ],
    'content_locales' => [
        'en' => 'English',
        'ar' => 'العربية',
        'fr' => 'Français',
    ],
    'default_content_locale' => 'en',
],
```

- **`ui_locales`** — drives the language switcher in the editor toolbar and which Laravel translation files are loaded for the builder chrome.
- **`content_locales`** — drives the language tabs shown by multilingual properties (`RichTextProperty`, `SimpleTextProperty`).
- **`default_content_locale`** — fallback locale used when content for the active locale doesn't exist.

The two arrays don't need to match — a common pattern is to keep the UI in English only while letting your editors author content in many languages.

---

## UI localization

The editor automatically loads the package's bundled JSON translations for whichever locale is active. To customize them, publish the translation files and edit:

```bash
php artisan vendor:publish --tag=page-builder-translations
```

This copies the JSON files to `lang/vendor/page-builder/`. To add a brand‑new language, create `lang/vendor/page-builder/{locale}.json` and translate the strings:

```json
{
    "Add Row": "Ajouter une rangée",
    "Pages": "Pages",
    "Save Page": "Enregistrer la page",
    "Add Block": "Ajouter un bloc"
}
```

The shipped `lang/en.json` inside the package is the canonical key list.

The package's `page-builder-localization` middleware (applied to every editor and render route) sets Laravel's app locale from the user's session, so anywhere you call `__('Some string')` it resolves against the right file.

---

## Multilingual content properties

Two property types support multilingual content out of the box:

- **`RichTextProperty`** — multilingual by default (Quill editor with per‑locale tabs)
- **`SimpleTextProperty`** — multilingual by default (plain text with per‑locale tabs)

```php
use Trinavo\LivewirePageBuilder\Support\Properties\RichTextProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\SimpleTextProperty;

public function getPageBuilderProperties(): array
{
    return [
        new RichTextProperty('content', __('Content')),                     // multilingual
        new SimpleTextProperty('headline', __('Headline')),                 // multilingual
        new SimpleTextProperty('alt_text', __('Alt text'), null, false),    // monolingual
    ];
}
```

Pass `false` as the last constructor argument to disable multilingual mode for an individual property — useful for things like URLs, slugs, alt text where one value applies to all locales.

### How the data is stored

A multilingual property is saved as a structured array:

```php
[
    'multilingual'   => true,
    'values'         => [
        'en' => 'English content...',
        'ar' => 'المحتوى العربي...',
        'fr' => 'Contenu français...',
    ],
    'default_locale' => 'en',
]
```

A monolingual property is saved as a plain scalar.

---

## Reading multilingual content

The `Block` base class already takes care of resolving the right locale for built‑in blocks. If your custom block reads a multilingual property directly, run it through `LocalizationService::getLocalizedValue()` (or the shorter `pb_localize_content()` helper):

```php
use Trinavo\LivewirePageBuilder\Services\LocalizationService;

public function render()
{
    $localization = app(LocalizationService::class);

    return view('livewire.blocks.hero', [
        // Use the current app locale
        'title' => $localization->getLocalizedValue($this->title),

        // Or force a specific locale
        'titleFr' => $localization->getLocalizedValue($this->title, 'fr'),
    ]);
}
```

`getLocalizedValue($content, ?string $locale = null)` returns:

1. `$content` unchanged if it isn't a multilingual structure
2. `values[$locale]` if present
3. `values[$default_locale]` otherwise
4. The first value in `values` as a last resort
5. `''` if `values` is empty

For convenience, the package also ships a helper:

```php
$title = pb_localize_content($this->title);          // current locale
$title = pb_localize_content($this->title, 'fr');    // specific locale
```

---

## Creating multilingual content programmatically

When seeding or migrating data, build the structure with `createMultilingualContent()`:

```php
$content = app(LocalizationService::class)->createMultilingualContent([
    'en' => 'Welcome',
    'ar' => 'مرحبا',
    'fr' => 'Bienvenue',
], defaultLocale: 'en');
```

This produces the same shape the editor saves.

---

## Runtime locale management

`LocalizationService` is a singleton — resolve it from the container and call its setters whenever you need to drive locales from a database or another source. Calling `shareWithViews()` afterwards re‑pushes the locale arrays to the package views in the current request.

```php
use Trinavo\LivewirePageBuilder\Services\LocalizationService;

public function boot(LocalizationService $localization): void
{
    // Replace the locale lists entirely
    $localization->setUiLocales(Locale::pluck('name', 'code')->all());
    $localization->setContentLocales(Locale::pluck('name', 'code')->all());

    // Or add/remove individual locales
    $localization->addUiLocale('de', 'German');
    $localization->addContentLocale('de', 'German');
    $localization->removeContentLocale('fr');

    // Change the fallback
    $localization->setDefaultContentLocale('en');

    // Switch the active UI locale and load its translations
    $localization->setUiLocale('fr');

    // Re‑share the locale arrays with the package views in this request
    $localization->shareWithViews();
}
```

Full method list (all return `self` and chain unless noted otherwise):

| Method | Purpose |
|---|---|
| `setUiLocales(array)` / `getUiLocales(): array` | Replace / read the UI locale map |
| `setContentLocales(array)` / `getContentLocales(): array` | Replace / read the content locale map |
| `addUiLocale(code, name)` / `removeUiLocale(code)` | Per‑entry mutations |
| `addContentLocale(code, name)` / `removeContentLocale(code)` | Per‑entry mutations |
| `setDefaultContentLocale(string)` / `getDefaultContentLocale(): string` | Default content locale |
| `setUiLocale(string)` | Switch the application's active UI locale and load its translations |
| `shareWithViews(): void` | Push the current state into the package views |
| `getLocalizedValue(mixed, ?string)` | Resolve a multilingual structure to one value |
| `createMultilingualContent(array, ?string): array` | Build a multilingual structure |
| `registerJsonTranslations(string)` / `registerJsonTranslationsForLocale(string, string)` | Add extra translation paths |
| `flushCache(): self` | Reload locale arrays from config |

---

## RTL languages

Tailwind 4 has first‑class RTL support via the `rtl:` variant. The package's bundled views already use `rtl:` where it matters; when overriding views, add `dir="rtl"` to your root layout for RTL locales and use `rtl:` to flip directional spacing / borders / icons.

---

## Best practices

- Keep `ui_locales` ⊆ `content_locales` (or the other way around) only if you really have a reason; otherwise mirroring the two lists is the least surprising default.
- Always set a `default_content_locale` — it's the fallback when a translation is missing.
- For text that's the same everywhere (URLs, slugs, alt text), use `SimpleTextProperty(..., multilingual: false)` so editors don't see a useless tab strip.
- For SEO‑sensitive content, fall back gracefully: the resolver already returns the default locale, then the first available value, so an empty translation never breaks the page.

---

## See also

- [Custom Block Development](custom-block-development.md) — the property types and how to expose them
- [Variables](variables.md) — variable substitution in localized content
