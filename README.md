# Livewire Page Builder

A visual, extensible page builder for **Laravel 12** and **Livewire 3**, built on **Tailwind CSS 4** + **DaisyUI**. Build pages from reusable blocks with a drag‑and‑drop editor, organise them into **themes**, preview them in mobile / tablet / desktop using real container queries, and ship them in multiple languages.

Any Livewire component can be registered as a block. Any page can be reused as a block. Layouts can be exported, encrypted, shared and imported as JSON files. The package ships ~13 property types so blocks expose typed UI controls (text, rich text, color, icon, image, video, responsive size & spacing, …) instead of raw form fields.

---

## ✨ Features

- **Visual editor** — drag‑and‑drop rows and blocks, reorder, copy/paste within or across pages (with automatic ID regeneration), context menus, property panel
- **Themes** — group pages under a named theme, set a default theme, clone themes, import/export, optional **AES‑256‑GCM theme encryption** with password protection
- **Layout templates** — ship pre‑built page layouts as JSON files and let users start from a template
- **Responsive by design** — every shared property has mobile / tablet / desktop variants resolved through Tailwind 4 `@3xl` / `@7xl` container queries; preview area uses real device widths
- **Built‑in blocks** — `RichText`, `SimpleText`, `Spacer`, `IconBlock`, plus core `RowBlock` and `BuilderPageBlock` (page‑as‑block)
- **Rich property catalog** — 13 property types covering text, rich text, color, icon, image, video, select, checkbox, responsive size, responsive spacing, plus a `CustomProperty` for shipping your own UI
- **Multilingual** — independent **UI locales** and **content locales**, language switcher in the editor, multilingual `RichTextProperty`, dynamic locale registration at runtime
- **Variables** — `{{variable}}` substitution inside text blocks. Register string or callable variables in config; built‑ins include `app_name`, `app_url`, `year`, `current_datetime`
- **Dark / light mode** — first‑class throughout the editor and the rendered output
- **Publishable everything** — config, views, translations and assets are all publishable for full customization
- **MIT licensed**

---

## 📋 Requirements

| | Minimum |
|---|---|
| PHP | 8.2 |
| Laravel | 12 |
| Livewire | 3.7 |
| Tailwind CSS | 4 |

---

## 🚀 Installation

```bash
composer require trinavo/livewire-page-builder
php artisan pagebuilder:install
```

The installer will:

1. Append the package's `@source` directive to `resources/css/app.css` so Tailwind sees all package Blade files
2. Publish `config/page-builder.php`
3. Offer to publish views (`page-builder-views` tag) and translations (`page-builder-translations` tag)
4. Run the package migrations (creates `builder_pages`, `builder_themes`, `builder_settings`)

Flags: `--force` overwrites published files, `--silent` accepts the defaults non‑interactively.

For a step‑by‑step manual installation, see [docs/advanced-configuration.md](docs/advanced-configuration.md).

---

## ⚡ Quick start

1. **Register a block** in `config/page-builder.php`:

   ```php
   use App\Livewire\Blocks\HeroBlock;

   'blocks' => [
       HeroBlock::class,
   ],
   ```

2. **List your pages**:

   ```php
   'pages' => [
       'home'   => ['label' => 'Home'],
       'about'  => ['label' => 'About'],
       'header' => ['is_block' => true, 'label' => 'Header'],
       'footer' => ['is_block' => true, 'label' => 'Footer'],
   ],
   ```

3. **Open the builder** at `/page-builder`. Create a theme, then open the editor for a page key.

---

## 🌐 Routes

All routes are prefixed with `/page-builder`. Editor routes use `editor_middleware` (default `['auth']`), the public render route uses `render_middleware` (default `[]`).

| Method | Path | Name | Description |
|---|---|---|---|
| GET | `/page-builder` | `page-builder.index` | Redirects to the theme manager |
| GET | `/page-builder/themes` | `page-builder.themes` | Theme manager UI |
| GET | `/page-builder/editor/{pageKey}/{themeId?}` | `page-builder.editor` | Visual editor for one page |
| GET | `/page-builder/preview/cancel` | `page-builder.preview.cancel` | Exit preview mode |
| GET | `/page-builder/page/view/{pageKey}/{themeId?}` | `page-builder.page.view` | Public rendered page |

You can also render a saved page anywhere in your own app:

```php
use Trinavo\LivewirePageBuilder\Services\PageBuilderRender;

Route::get('/{permalink}', function (string $permalink) {
    return app(PageBuilderRender::class)->renderPage($permalink);
});
```

---

## ⚙️ Configuration

`config/page-builder.php` keys:

| Key | Default | Purpose |
|---|---|---|
| `editor_middleware` | `['auth']` | Middleware on editor routes. Tighten with a gate, e.g. `['auth', 'can:edit-pages']` |
| `render_middleware` | `[]` | Middleware on the public render route. Add `['auth']` for gated content |
| `strict_css_validation` | `true` | Drop inline CSS values that don't match expected color/url/keyword shapes (logs a warning) |
| `localization.ui_locales` | `['en' => 'English']` | Builder interface languages |
| `localization.content_locales` | `['en' => 'English']` | Languages selectable in multilingual property editors |
| `localization.default_content_locale` | `'en'` | Locale assigned to new content |
| `encryption.enabled` | `env('PAGE_BUILDER_ENCRYPTION_ENABLED', false)` | Toggle theme export encryption |
| `encryption.key` | `env('PAGE_BUILDER_ENCRYPTION_KEY', '')` | Encryption key (set in `.env`) |
| `encryption.file_extension` | `'.tet'` | Extension for encrypted theme files |
| `encryption.require_password` | `true` | Require a password for encrypted theme files |
| `blocks` | `[]` | Array of block class strings to register |
| `pages` | `[]` | Page key → options map (`label`, `is_block`) |
| `layouts` | `[]` | Absolute paths to JSON layout templates |
| `variables` | `[]` | Name → value (string or `Closure`) map of template variables |

---

## 🎨 Themes & pages

Pages are identified by **`(key, theme_id)`** — the same `home` key can have a different design under each theme. The theme manager (`/page-builder/themes`) lets you create, clone, set a default, import and export themes. The default theme id is stored in the `builder_settings` table and is used whenever you call the editor or renderer without an explicit `themeId`.

Any page can also be a **reusable block** by adding `'is_block' => true` to its config entry. Such pages don't render on their own; instead they show up in the block picker as `BuilderPageBlock`s and can be embedded inside other pages. This is how header / footer / sidebar are typically built.

---

## 🧩 Creating a custom block

A block is just a Livewire component that extends the package's `Block` base class.

```php
namespace App\Livewire\Blocks;

use Trinavo\LivewirePageBuilder\Support\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\RichTextProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ImageProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ColorProperty;

class HeroBlock extends Block
{
    public $title    = 'Welcome';
    public $subtitle = '';
    public $image    = null;
    public $bgColor  = 'primary';

    public function getPageBuilderLabel(): string    { return __('Hero'); }
    public function getPageBuilderCategory(): string { return __('Content'); }
    public function getPageBuilderIcon(): string     { return 'heroicon-o-photo'; }

    public function getPageBuilderProperties(): array
    {
        return [
            new TextProperty('title', __('Title'), false, $this->title),
            new RichTextProperty('subtitle', __('Subtitle'), $this->subtitle, true),
            new ImageProperty('image', __('Background image'), $this->image),
            new ColorProperty('bgColor', __('Background color'), 'primary'),
        ];
    }

    public function render()
    {
        return view('livewire.blocks.hero');
    }
}
```

Register it in `config/page-builder.php` and it appears in the block picker.

The `Block` base class also gives every block a full set of **shared responsive properties** — width, height, min‑height, padding/margin on all four sides, typography, text & background colors, gradients, background image, borders, border radius, box shadow, filters, backdrop filter, transforms (rotate/scale/translate), position and z‑index — each with mobile / tablet / desktop variants. These are merged into the property panel automatically; you don't need to declare them.

For full details (icons, categories, custom property views, advanced patterns) see [docs/custom-block-development.md](docs/custom-block-development.md).

---

## 🏷️ Property types

All property classes live in `Trinavo\LivewirePageBuilder\Support\Properties\`:

| Class | Renders | Notes |
|---|---|---|
| `TextProperty` | Single‑line text input | Supports `numeric` mode with min/max |
| `RichTextProperty` | WYSIWYG (Quill) editor | Optional **multilingual** mode (last constructor arg) |
| `SimpleTextProperty` | Lightweight text input | Used for plain text without rich formatting |
| `CheckboxProperty` | Toggle | |
| `SelectProperty` | Dropdown | Pass an `options` array `[value => label]` |
| `ColorProperty` | Color picker | Accepts hex, rgb, DaisyUI semantic names (e.g. `'primary'`) |
| `ImageProperty` | Image picker / uploader | |
| `VideoProperty` | Video URL / embed | |
| `IconProperty` | Icon picker | Backed by `blade-heroicons` + `blade-bootstrap-icons` |
| `FlexibleSizeProperty` | Mobile / tablet / desktop size triplet | For width / height‑style values |
| `ResponsiveSpacingProperty` | 4‑sided spacing × 3 breakpoints | For padding / margin grids |
| `CustomProperty` | Your own Blade view | Pass the view path to the constructor |
| `BlockProperty` | Base class | Extend it to define entirely new property types |

---

## 🧱 Built‑in blocks

Shipped under `Trinavo\LivewirePageBuilder\Blocks\`:

- **`RichText`** — formatted HTML (Quill output, sanitized & class‑normalized for Tailwind)
- **`SimpleText`** — plain inline text with localization + variable substitution
- **`Spacer`** — empty vertical space with responsive heights
- **`IconBlock`** — icon picker rendered to an SVG icon

System blocks (always available):

- **`RowBlock`** — flex container for horizontal/vertical layout, holds child blocks
- **`BuilderPageBlock`** — embeds another `is_block` page as a block

---

## 🌍 Multilingual UI & content

The package separates **UI language** (editor chrome) from **content language** (the actual words your editors type). Configure both in `config/page-builder.php`:

```php
'localization' => [
    'ui_locales'             => ['en' => 'English', 'ar' => 'العربية'],
    'content_locales'        => ['en' => 'English', 'ar' => 'العربية'],
    'default_content_locale' => 'en',
],
```

When more than one content locale is configured, multilingual properties (e.g. `RichTextProperty` with the multilingual flag enabled) show tabs per language. Content is stored in a structured shape that preserves all translations:

```php
[
    'multilingual'   => true,
    'values'         => ['en' => '...', 'ar' => '...'],
    'default_locale' => 'en',
]
```

You can also drive locales at runtime (e.g. from a database) via the `LocalizationService`:

```php
use Trinavo\LivewirePageBuilder\Services\LocalizationService;

public function boot(LocalizationService $localization)
{
    $localization->setUiLocales(Locale::pluck('name', 'code')->all());
    $localization->setContentLocales(Locale::pluck('name', 'code')->all());
    $localization->setDefaultContentLocale('en');
    $localization->shareWithViews();
}
```

Full guide: [docs/multilingual-support.md](docs/multilingual-support.md).

---

## 🧬 Variables

Use `{{variable}}` placeholders inside text content and they'll be substituted at render time. Variables can be plain strings or closures:

```php
'variables' => [
    'company_name'  => 'Acme Inc',
    'support_email' => fn () => config('mail.support'),
],
```

Built‑in variables registered by the package: `app_name`, `app_url`, `year`, `current_datetime`. Full reference: [docs/variables.md](docs/variables.md).

---

## 🗂️ Layouts

A **layout** is a JSON file containing pre‑built rows and blocks that editors can import as a starting point for a new page. Register layout files in config:

```php
'layouts' => [
    resource_path('page-builder/layouts/landing.json'),
    resource_path('page-builder/layouts/blog-post.json'),
],
```

Each layout file has the shape:

```json
{
    "meta": {
        "name":        { "en": "Landing", "ar": "هبوط" },
        "description": { "en": "A simple landing page",  "ar": "..." }
    },
    "components": [ /* rows and blocks */ ]
}
```

The editor's layout picker reads `meta.name` / `meta.description` per the current UI locale.

---

## 🔐 Theme import / export & encryption

Themes can be exported as JSON and imported into another installation. Enable encryption to export themes as opaque `.tet` files protected by your encryption key (and optionally a password). See:

- [docs/theme-service-usage.md](docs/theme-service-usage.md) — programmatic import/export, cloning, replacing pages
- [docs/theme-encryption.md](docs/theme-encryption.md) — AES‑256‑GCM encryption, key management, password protection

Facades available out of the box: `ThemeService`, `ThemeEncryptionService`, `PageBuilderVariables`.

---

## 🪝 Events & extension points

- **Events**: `BuilderPageSaved` (after a page is saved), `DefaultThemeSet` (when the default theme changes)
- **UI hooks**: `PageBuilderUIService` (singleton) exposes hooks for customising the editor chrome
- **Views**: every view in the package can be overridden by publishing with the `page-builder-views` tag
- **Translations**: override per‑string via `lang/vendor/page-builder` after publishing `page-builder-translations`
- **Blade component namespace**: `<x-page-builder::… />` resolves to `Trinavo\LivewirePageBuilder\View\Components`

---

## 🧰 Safe Tailwind class generation

Because page content lives in the database, Tailwind's static scanner can't see every class your editors might pick. To keep the production CSS small without losing those classes, the package ships [`scripts/generate_safe_classes.php`](scripts/generate_safe_classes.php) — run it to produce a safelist of classes that the package actually emits from properties.

---

## 📚 Documentation

The [`docs/`](docs/) folder contains the deep dives:

- [Advanced configuration](docs/advanced-configuration.md)
- [Custom block development](docs/custom-block-development.md)
- [Multilingual support](docs/multilingual-support.md)
- [Variables](docs/variables.md)
- [Theme service usage](docs/theme-service-usage.md)
- [Theme encryption](docs/theme-encryption.md)
- [Performance optimization](docs/performance-optimization.md)

---

## ☕ Support

If this package helps you, consider supporting the development:

[![Buy Me A Coffee](https://img.shields.io/badge/Buy%20Me%20A%20Coffee-☕-orange.svg?style=flat-square)](https://buymeacoffee.com/doonfrs)

**Your support helps maintain and improve this package!**

---

## 📝 License

MIT — see [`composer.json`](composer.json).
