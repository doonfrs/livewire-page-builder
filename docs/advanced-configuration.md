# Advanced Configuration

Everything documented here lives in `config/page-builder.php`. The package merges that file with its own defaults at boot, so you can keep your config file slim and only override what you need.

---

## The full config schema

| Key | Default | Notes |
|---|---|---|
| `editor_middleware` | `['auth']` | Middleware on the editor routes (`/page-builder`, `/page-builder/themes`, `/page-builder/editor/...`, `/page-builder/preview/cancel`) |
| `render_middleware` | `[]` | Middleware on the public render route (`/page-builder/page/view/...`) |
| `strict_css_validation` | `true` | Drop inline‑style CSS values that don't match expected color / url / keyword shapes (logs a warning) |
| `localization.ui_locales` | `['en' => 'English']` | Languages the editor chrome can be displayed in |
| `localization.content_locales` | `['en' => 'English']` | Languages selectable in multilingual property editors |
| `localization.default_content_locale` | `'en'` | Locale assigned to new content |
| `encryption.enabled` | `env('PAGE_BUILDER_ENCRYPTION_ENABLED', false)` | Toggle theme‑export encryption |
| `encryption.key` | `env('PAGE_BUILDER_ENCRYPTION_KEY', '')` | Base64‑encoded 32‑byte key — set in `.env` |
| `encryption.file_extension` | `'.tet'` | Filename suffix for encrypted theme exports |
| `encryption.require_password` | `true` | Require a per‑export password (in addition to the configured key) |
| `blocks` | `[]` | Array of fully‑qualified block class names |
| `pages` | `[]` | Map of page key → options (`label`, `is_block`) |
| `layouts` | `[]` | Array of absolute paths to JSON layout templates |
| `variables` | `[]` | Map of variable name → string value (closures aren't allowed here — see [Variables](variables.md)) |

---

## Pages

Each entry in `pages` is keyed by the page's URL slug / identifier. The value is an options array:

```php
'pages' => [
    'home'   => ['label' => 'Home'],
    'about'  => ['label' => 'About'],
    'header' => ['is_block' => true, 'label' => 'Header'],
    'footer' => ['is_block' => true, 'label' => 'Footer'],
],
```

Options:

- `label` *(string)* — display name shown in the page selector. Defaults to the key.
- `is_block` *(bool)* — when `true`, the page is hidden from the public site and instead appears in the block picker as a `BuilderPageBlock`. Used for headers, footers, sidebars, repeated sections.

The pair `(page key, theme id)` uniquely identifies a stored `BuilderPage` row, so the same `home` key can have completely different content under each theme.

---

## Blocks

Just list your block class names:

```php
'blocks' => [
    App\Livewire\Blocks\HeroBlock::class,
    App\Livewire\Blocks\FeatureCard::class,
],
```

The service provider takes each class and registers it as a Livewire component with a kebab‑case alias derived from its FQCN (e.g. `App\Livewire\Blocks\HeroBlock` → `page-builder-app-livewire-blocks-hero-block`). You **do not** need to call `Livewire::component()` yourself.

See [Custom Block Development](custom-block-development.md) for how to author blocks.

---

## Layouts (JSON page templates)

A layout is a JSON file containing pre‑built rows and blocks that editors can import as the starting point for a page.

```php
'layouts' => [
    resource_path('page-builder/layouts/landing.json'),
    resource_path('page-builder/layouts/blog-post.json'),
],
```

The package reads each file via `PageBuilderService::getAvailableLayouts($locale)` and exposes the resulting list to the editor's layout picker. Each file must have this shape:

```json
{
    "meta": {
        "name":        { "en": "Landing",                "ar": "هبوط" },
        "description": { "en": "A simple landing page",  "ar": "..." }
    },
    "components": [
        /* rows and blocks in the same shape as a saved page */
    ]
}
```

`meta.name` and `meta.description` are looked up by the current UI locale, falling back to `en`, then to `basename($path)`.

Files that don't exist on disk, or don't contain a top‑level `components` key, are silently skipped — handy if you ship environment‑specific layouts.

---

## Middleware

Two independent config keys gate the editor and the public render route:

```php
'editor_middleware' => ['auth', 'can:edit-pages'],
'render_middleware' => [],   // public by default
```

The package always prepends `'web'` and `'page-builder-localization'`, so you only need to add the application‑specific layers.

Common patterns:

| Goal | Configuration |
|---|---|
| Anyone can view, only authenticated admins can edit | `editor_middleware => ['auth', 'can:edit-pages']`, `render_middleware => []` |
| Gated content (signed‑in users only) | `editor_middleware => ['auth', 'can:edit-pages']`, `render_middleware => ['auth']` |
| Open builder demo (don't do this in production) | `editor_middleware => []` |

The `page-builder-localization` middleware switches Laravel's locale to the user's selected UI locale for the duration of the request.

---

## Publishing & overriding views

The service provider exposes three publish tags. None of them are mandatory — the package falls back to its bundled versions for any view you haven't published.

| Tag | Destination | What it contains |
|---|---|---|
| `config` | `config/page-builder.php` | The config file documented on this page |
| `page-builder-views` | `resources/views/vendor/page-builder/` | All Blade views the package renders |
| `page-builder-translations` | `lang/vendor/page-builder/` | UI translation JSON files |

```bash
php artisan vendor:publish --tag=page-builder-views
php artisan vendor:publish --tag=page-builder-translations
```

After publishing, any file you keep is used **instead of** the package version; any file you delete falls back to the package default. That way you can override the few views you actually need and still receive package updates for the rest.

The most commonly overridden views:

- `view-page.blade.php` — wraps the rendered public page. Change this to swap in your own `<x-app-layout>` or HTML shell.
- `layouts/app.blade.php` — wraps the builder UI itself (fonts, head tags, etc.).
- `components/row-view.blade.php` — frontend row rendering.

---

## Manual installation (if you skip `pagebuilder:install`)

Most users should just run `php artisan pagebuilder:install` — it does all of this for you. The manual steps are only listed here for completeness or for unusual setups (e.g. CI pipelines that need to be fully non‑interactive).

```bash
composer require trinavo/livewire-page-builder
php artisan vendor:publish --tag=config
php artisan vendor:publish --tag=page-builder-views          # optional
php artisan vendor:publish --tag=page-builder-translations   # optional
php artisan migrate
```

Then add this line to your `resources/css/app.css` so Tailwind scans the package's Blade files:

```css
@source '../../vendor/trinavo/livewire-page-builder/resources/**/*.php';
```

And rebuild your CSS bundle.

---

## See also

- [Custom Block Development](custom-block-development.md)
- [Multilingual Support](multilingual-support.md)
- [Variables](variables.md)
- [Theme Service Usage](theme-service-usage.md)
- [Theme Encryption](theme-encryption.md)
