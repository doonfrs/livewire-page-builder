# Performance Optimization

The Livewire Page Builder is designed with performance as a key consideration. We follow a dual-mode approach to ensure optimal performance in both editing and production environments.

## Architecture Overview

The page builder operates in two distinct modes:

1. **Builder Mode:** Uses Livewire components for interactive editing
2. **View Mode:** Uses optimized Blade templates for production rendering

### Blade-Based Rendering

The view mode uses Blade templates instead of Livewire components:

- `resources/views/view-page.blade.php`: Main page rendering template
- `resources/views/components/row-view.blade.php`: Handles row rendering
- `resources/views/components/builder-page-block-view.blade.php`: Renders nested page blocks

This approach eliminates the Livewire overhead for end users viewing your site, resulting in:

- Faster page loads
- Reduced memory usage
- Lower server processing requirements

### Pre-compiled CSS Classes and Styles

In view mode, all CSS classes and inline styles are pre-generated and directly applied in the Blade templates:

```blade
<div class="{{ $row['cssClasses'] }}" style="{{ $row['inlineStyles'] }}">
    <!-- Content here -->
</div>
```

This eliminates the need for dynamic class and style calculations at runtime.

### Query Caching

Page data is cached to minimize database queries:

```php
// Example of caching implementation
public function getRenderedRows()
{
    return cache()->remember('page_rows_' . $this->id, now()->addHours(24), function () {
        return $this->processRowsForRendering();
    });
}
```

### Optimized DOM Updates

We use key attributes and optimize our templates to minimize DOM updates:

```blade
<div>
    @foreach($blocks as $index => $block)
        <livewire:builder-block :block="$block" :key="$block->id" />
    @endforeach
</div>
```

## Best Practices for Custom Blocks

When creating custom blocks, follow these guidelines for optimal performance:

1. **Minimize Public Properties:** Only expose what's needed for editing
2. **Use Computed Properties:** Calculate values on-demand instead of storing them
3. **Optimize Blade Templates:** Keep your templates lean and efficient
4. **Follow Livewire 3 Practices:** Consult the [Livewire 3 performance documentation](https://livewire.laravel.com/docs/computed-properties#performance-advantage)

By following these practices, your page builder implementation will maintain high performance even with complex pages and numerous custom blocks.
