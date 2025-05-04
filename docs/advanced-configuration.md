# Advanced Configuration

## Configuring Pages

Define your site structure in `config/page-builder.php` under the `pages` key. You can use simple strings for page names or key-value arrays for advanced options:

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

- Use `'is_block' => true` to make a page available as a reusable block.
- Use `'label'` to customize the display name in the UI.

## Registering Blocks

List your custom blocks in the `blocks` key:

```php
'blocks' => [
    'hero' => App\Http\Livewire\Blocks\HeroBlock::class,
    'feature' => App\Http\Livewire\Blocks\FeatureBlock::class,
],
```

## Organizing Large Projects

- Group related blocks in subfolders and use namespaces.
- Use descriptive labels and icons for blocks.
- Use the `is_block` option to create reusable sections (e.g., header, footer).

## Advanced Layouts with Container Queries

- Use [Tailwind CSS container queries](https://tailwindcss.com/docs/responsive-design#what-are-container-queries) for responsive layouts in the editor.
- Set the preview container width to match Tailwind's breakpoints for accurate device simulation.

## Customizing Layouts

By default, rendered pages use `<x-app-layout>`. If you want to change the layout:

1. **Publish the package views:**

   ```bash
   php artisan vendor:publish --provider="Trinavo\LivewirePageBuilder\Providers\PageBuilderServiceProvider" --tag=views
   ```

2. **Edit the rendered layout:**
   - Modify `resources/views/vendor/page-builder/view-page.blade.php` to use your desired layout component or Blade structure.
3. **Edit the builder/editor layout:**
   - Change `resources/views/layouts/app.blade.php` to update the builder UI (fonts, colors, etc).

> **Tip:** Only publish and modify the views you need to change. Unmodified views will continue to use the package defaults, ensuring you get updates and bug fixes from future package releases.

---

See [Custom Block Development](custom-block-development.md) for more on building your own blocks.
