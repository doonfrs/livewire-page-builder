# Livewire Page Builder

## Introduction

**Livewire Page Builder** is a powerful, modern, and extensible page builder package for Laravel 12 and Livewire 3. It empowers developers and content editors to visually create, edit, and manage dynamic pages using a drag-and-drop interface, reusable blocks, and a fully customizable structure—all with the speed and flexibility of Livewire and Tailwind CSS.

---

## ✨ Features

- **Visual Drag-and-Drop Editor**: Build pages visually with rows and blocks, reorder content, and see changes instantly.
- **Custom Blocks**: Easily register your own Livewire components as blocks, with full support for custom properties and icons.
- **Reusable Sections**: Use any page as a block (e.g., header, footer) for maximum reusability and maintainability.
- **Device Preview Modes**: Instantly preview your page in mobile, tablet, and desktop modes using real container queries.
- **Tailwind 4 & Container Queries**: Leverage the latest Tailwind CSS features for pixel-perfect responsive design in the editor.
- **Dark/Light Mode**: Fully supports both dark and light themes for a professional editing experience.
- **Property Panel**: Edit block properties in a dedicated, always-visible panel with support for text, image, checkbox, and more.
- **Context Menus & Toolbars**: Intuitive UI for block/row actions, including context menus, toolbars, and modals.
- **Configurable Site Structure**: Define your site's pages, blocks, and structure in a single config file.
- **Publishable Views**: Easily override any package view for full customization, while keeping unmodified views up-to-date.
- **Multilingual Support**: Built-in translation system for internationalizing the UI in multiple languages.
- **Extensible & Developer-Friendly**: Add new property types, extend the builder, and integrate with other Livewire or Laravel packages.
- **MIT Licensed & Open Source**: Use it freely in any project, commercial or personal.

---

## 🏁 Getting Started

1. **Publish the config file:**
   - Run the following command to publish the package configuration:

     ```bash
     php artisan vendor:publish --provider="Trinavo\LivewirePageBuilder\Providers\PageBuilderServiceProvider"
     ```

2. **Modify your Livewire components:**
   - Change your block components to extend `Block` instead of `Component`:

     ```php
     use Trinavo\LivewirePageBuilder\Block;
     
     class HeroBlock extends Block
     {
         // ...
     }
     ```

3. **Register your blocks:**
   - Add your Livewire block component class to the `blocks` list in `config/page-builder.php`:

     ```php
     'blocks' => [
         'hero' => App\Http\Livewire\Blocks\HeroBlock::class,
         // ...
     ],
     ```

4. **List your website pages:**
   - Add your pages to the `pages` array in `config/page-builder.php`:

     ```php
     'pages' => [
         'home',
         'about',
         // ...
     ],
     ```

5. **Start building!**
   - Visit `/page-builder` in your browser to use the visual editor.

---

## 🚀 Fast Installation

1. **Install via Composer:**

   ```bash
   composer require trinavo/livewire-page-builder
   ```

2. **Run the installer:**

   ```bash
   php artisan pagebuilder:install
   ```

   This command will:
   - Add the required Tailwind source path to your CSS
   - Publish configuration files
   - Copy built assets to the correct location
   - Optionally publish views, translations, and run migrations

3. **Register your blocks and configure your pages:**

   Edit `config/page-builder.php` to add your blocks and pages:

   ```php
   // config/page-builder.php
   'blocks' => [
       'hero' => App\Http\Livewire\Blocks\HeroBlock::class,
   ],
   'pages' => [
       'home',
       'about',
       // ...
   ],
   ```

4. **Access the builder:**

   Visit `/page-builder` in your browser to start using the visual editor.

---

## 🛠️ Manual Installation (if needed)

If you prefer more control over the installation process:

1. **Install via Composer:**

   ```bash
   composer require trinavo/livewire-page-builder
   ```

2. **Publish configuration:**

   ```bash
   php artisan vendor:publish --tag=config --provider="Trinavo\LivewirePageBuilder\Providers\PageBuilderServiceProvider" 
   ```

3. **Publish views (optional):**

   ```bash
   php artisan vendor:publish --tag=page-builder-views --provider="Trinavo\LivewirePageBuilder\Providers\PageBuilderServiceProvider"
   ```

4. **Publish translations (optional):**

   ```bash
   php artisan vendor:publish --tag=page-builder-translations --provider="Trinavo\LivewirePageBuilder\Providers\PageBuilderServiceProvider"
   ```

5. **Publish assets:**

   ```bash
   php artisan vendor:publish --tag=page-builder-assets --provider="Trinavo\LivewirePageBuilder\Providers\PageBuilderServiceProvider"
   ```

6. **Add Tailwind source path:**

   Add this line to your CSS file:

   ```css
   @source '../../vendor/trinavo/livewire-page-builder/resources/**/*.blade.php';
   ```

7. **Run migrations:**

   ```bash
   php artisan migrate
   ```

---

## 🧑‍💻 Usage

- Access the page builder at `/page-builder` (or your configured route).
- Use the visual editor to add, move, and configure blocks and rows.
- Preview your page in mobile, tablet, and desktop modes.

---

## 🟣 **Container Queries for Preview**

> **Recommendation:** For the best preview experience, use [Tailwind CSS container queries](https://tailwindcss.com/docs/responsive-design#what-are-container-queries) (`@container`, `@md:`, `@lg:`) instead of classic `md:`, `sm:`, `lg:` responsive classes. This ensures that block visibility and layout respond to the preview container size, not the viewport.
>
> - In the editor, the preview area uses `@container` and device widths (e.g., `w-[375px]`, `w-[768px]`, `w-[1280px]`).
> - For rendering on the live site, classic responsive classes (`md:`, `lg:`) work as expected.

---

## 🧩 Custom Blocks & Reusable Pages

### **Block as Page & Page as Block**

- Any page can be reused as a block (e.g., header, footer) by setting the `is_block` option in your config.
- This allows you to build complex layouts with reusable sections.

### **Configuring Website Structure**

Define your site structure in `config/page-builder.php`:

```php
'pages' => [
    'home',
    'about',
    'header' => [
        'is_block' => true,
        'label' => 'Header',
    ],
    'footer' => [
        'is_block' => true,
        'label' => 'Footer',
    ],
],
```

- Use a string for simple pages, or a key-value array for advanced options.
- Set `'is_block' => true` to make a page available as a block in other pages.

---

## 🧬 Registering Custom Blocks

- List your Livewire block components in the `blocks` section of your config:

```php
'blocks' => [
    'hero' => App\Http\Livewire\Blocks\HeroBlock::class,
    'feature' => App\Http\Livewire\Blocks\FeatureBlock::class,
    // ...
],
```

- **Important:** Your block components should extend the provided `Block` base class, not the default Livewire `Component`.

```php
use Trinavo\LivewirePageBuilder\Block;

class HeroBlock extends Block
{
    // ...
}
```

---

## 🏷️ Block Metadata & Properties

### **Block Metadata**

- `getPageBuilderIcon()`: Return a Blade icon component for the block selector.
- `getPageBuilderLabel()`: Return a human-readable label for the block.

### **Block Properties**

- Define editable properties for your block by returning an array of property objects (not arrays) in `getPageBuilderProperties()`:

```php
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ImageProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\CheckboxProperty;

public function getPageBuilderProperties(): array
{
    return [
        new CheckboxProperty('featured', 'Featured'),
        new TextProperty('title', 'Title'),
        new ImageProperty('image', 'Image'),
    ];
}
```

#### **Available Property Types**

- `TextProperty` — text input (optionally numeric, min/max)
- `ImageProperty` — image upload/selector
- `CheckboxProperty` — boolean toggle

You can also create your own property types by extending `BlockProperty`.

#### **Shared Properties**

- The base `Block` class provides shared properties for grid size and device visibility (mobile, tablet, desktop). These are automatically included in the builder UI.

---

## 📚 More Documentation

See the `docs/` folder for:

- Advanced configuration
- Custom block development
- Extending the builder
- UI/UX best practices

---

## 📝 License

MIT

---

## 🌐 Translations

Livewire Page Builder supports translations through Laravel's JSON localization system. The package comes with translation files in the `lang` directory.

### Using Translations

To access translations in your views, simply use the standard Laravel `__()` helper:

```php
{{ __('Move Up') }}
```

JSON translations are automatically loaded from the package's `lang` directory, and Laravel will look for the matching keys in the JSON files based on the current locale.

### Publishing Translations

You can publish the package's translation files to your application to customize them:

```bash
php artisan vendor:publish --tag=page-builder-translations --provider="Trinavo\LivewirePageBuilder\Providers\PageBuilderServiceProvider"
```

This will copy the translation files to the `lang/vendor/page-builder` directory of your application.

### Adding New Translations

To add new translations for additional languages:

1. Create a JSON file in the `lang` directory with the language code (e.g., `fr.json`, `de.json`).
2. Add your translations in JSON format:

```json
{
    "Move Up": "Déplacer vers le haut",
    "Move Down": "Déplacer vers le bas",
    "Add Block Before": "Ajouter un bloc avant",
    "Add Block After": "Ajouter un bloc après",
    "Delete": "Supprimer",
    "Select": "Sélectionner",
    "Row Actions": "Actions de ligne",
    "Add Row After": "Ajouter une ligne après",
    "Add Row Before": "Ajouter une ligne avant"
}
```

The translations will be automatically picked up by the application based on the user's locale.
