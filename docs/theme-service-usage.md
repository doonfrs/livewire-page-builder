# Theme Service Usage

`ThemeService` is the programmatic entry point for everything that happens in the **Themes** page of the editor: exporting, importing, cloning, replacing pages inside a theme. Use it from controllers, jobs, Artisan commands, or anywhere you want to manage themes without going through the UI.

It's available as a service binding **and** a facade — pick whichever fits your code style.

```php
use Trinavo\LivewirePageBuilder\Services\ThemeService;
use Trinavo\LivewirePageBuilder\Facades\ThemeService as ThemeServiceFacade;
use Trinavo\LivewirePageBuilder\Models\Theme;

// Container-resolved
$service = app(ThemeService::class);

// Dependency injection
public function __construct(private ThemeService $themes) {}

// Facade
ThemeServiceFacade::exportTheme(1);
```

Every method accepts a `Theme` model **or** an integer theme id — pass whichever you have.

---

## Exporting

### To an array

```php
$data = $service->exportTheme(1);

// $data:
// [
//     'name'        => 'Default',
//     'description' => '...',
//     'settings'    => ['slider_images' => ['desktop' => ['width' => 2280]]],  // theme settings JSON, null when unset
//     'pages'       => [ ['key' => 'home', 'components' => [...], 'is_block' => false, ...], ... ],
//     'exported_at' => '2026-05-14T...',
//     'version'     => '1.0',
// ]
```

Returns `null` if the theme doesn't exist (and logs a warning).

### To a JSON string

```php
$json = $service->exportThemeAsJson(1);
// Pretty-printed JSON by default. If encryption is enabled,
// returns the encrypted envelope instead.

$compact = $service->exportThemeAsJson(1, JSON_UNESCAPED_UNICODE);
```

### To a file

```php
// Saves to storage/app/themes/ by default
$path = $service->exportThemeToFile(1);

// Save somewhere else
$path = $service->exportThemeToFile(1, storage_path('exports/themes'));
```

The output file extension automatically becomes `.json` or the configured encrypted extension (default `.tet`) depending on whether encryption is enabled.

### Forcing an encrypted export

These bypass the "is encryption enabled" check — useful for selectively encrypting a single export regardless of the global setting. They throw if no encryption key is configured.

```php
$encryptedJson = $service->exportThemeAsEncryptedJson(1, password: 'optional-extra-password');
$path          = $service->exportThemeToEncryptedFile(1, directory: null, password: null);
```

---

## Importing

### From a PHP array

```php
$theme = $service->importTheme([
    'name'        => 'My New Theme',
    'description' => 'Imported from somewhere',
    'pages'       => [
        ['key' => 'home', 'components' => [/* rows */], 'is_block' => false],
    ],
], overwriteExisting: false);
```

- When `overwriteExisting` is `false` (default), a theme with a clashing name is renamed (`My New Theme (2)` etc.).
- When `true`, the existing theme's pages are replaced.
- A `settings` key in the data (see [Theme settings](advanced-configuration.md#theme-settings)) is restored onto the new theme; older exports without it import fine.

Returns the resulting `Theme` model, or `null` on failure.

### From a file

```php
// Auto-detects encrypted vs. plain JSON
$theme = $service->importThemeFromFile(storage_path('themes/landing.json'));
$theme = $service->importThemeFromFile(storage_path('themes/landing.tet'));   // also works

// Overwrite an existing theme with the same name
$theme = $service->importThemeFromFile($path, overwriteExisting: true);
```

### Forcing encrypted import (no auto-detection)

```php
$theme = $service->importEncryptedTheme($encryptedJsonString, overwriteExisting: false, password: null);
$theme = $service->importThemeFromEncryptedFile($filePath, overwriteExisting: false, password: null);
```

---

## Cloning

```php
$copy = $service->cloneTheme(1, 'My Cloned Theme');
```

Duplicates the theme **and** all its pages, including its [theme settings](advanced-configuration.md#theme-settings). The new theme has an auto‑assigned id. Returns the cloned `Theme` model, or `null` if the source doesn't exist.

---

## Replacing pages inside a theme

Sometimes you want to import a partial set of pages over an existing theme — for example, deploying updated header / footer markup without touching anything else.

```php
$theme = Theme::find(1);

// Replace every page in $theme with the pages from this data array.
// Missing pages stay; pages with matching keys are replaced.
$count = $service->replacePagesInTheme($theme, [
    ['key' => 'header', 'components' => [/* ... */], 'is_block' => true],
    ['key' => 'footer', 'components' => [/* ... */], 'is_block' => true],
]);

// Replace only the listed page keys
$count = $service->replaceSelectedPagesInTheme($theme, $pagesArray, ['header', 'footer']);
```

Both return the number of pages that were actually replaced.

---

## Other helpers

| Method | Purpose |
|---|---|
| `isEncryptionEnabled(): bool` | Whether `ThemeEncryptionService` will encrypt new exports |
| `getEncryptionService(): ThemeEncryptionService` | The underlying encryption service (for advanced use) |

---

## Error handling

Methods return `null` on recoverable failure (logged via `Log::warning` / `Log::error`). Encryption failures throw `ThemeEncryptionException`; malformed import data throws `InvalidThemeFormatException`; file I/O issues throw `ThemeFileException`. Catch the parents if you need to differentiate:

```php
use Trinavo\LivewirePageBuilder\Exceptions\ThemeEncryptionException;
use Trinavo\LivewirePageBuilder\Exceptions\InvalidThemeFormatException;
use Trinavo\LivewirePageBuilder\Exceptions\ThemeFileException;

try {
    $theme = $service->importThemeFromFile($path);
} catch (ThemeEncryptionException $e) {
    // wrong key, missing key, tampered ciphertext, etc.
} catch (InvalidThemeFormatException $e) {
    // valid JSON but not a theme export
} catch (ThemeFileException $e) {
    // file missing / unreadable
}
```

---

## Examples

### Artisan command — export a theme to disk

```php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Trinavo\LivewirePageBuilder\Services\ThemeService;

class ExportTheme extends Command
{
    protected $signature = 'theme:export {theme_id} {--out=}';

    public function handle(ThemeService $service): int
    {
        $path = $service->exportThemeToFile(
            (int) $this->argument('theme_id'),
            $this->option('out') ? dirname($this->option('out')) : null,
        );

        if (! $path) {
            $this->error('Theme not found or export failed.');
            return self::FAILURE;
        }

        $this->info("Exported to {$path}");
        return self::SUCCESS;
    }
}
```

### HTTP endpoint — import on upload

```php
public function import(Request $request, ThemeService $service)
{
    $file = $request->file('theme')->getRealPath();

    $theme = $service->importThemeFromFile($file, overwriteExisting: $request->boolean('overwrite'));

    abort_unless($theme, 422, 'Import failed');

    return response()->json(['theme_id' => $theme->id]);
}
```

---

## See also

- [Theme Encryption](theme-encryption.md) — encryption pipeline, key management
- [Advanced Configuration](advanced-configuration.md) — the `encryption.*` config keys
