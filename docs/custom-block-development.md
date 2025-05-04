# Custom Block Development

## Creating a Custom Block

To create a custom block, extend the provided `Block` base class instead of the default Livewire `Component`:

```php
use Trinavo\LivewirePageBuilder\Block;

class HeroBlock extends Block
{
    // ...
}
```

## Block Metadata

- `getPageBuilderIcon()`: Return a Blade icon component for the block selector.
- `getPageBuilderLabel()`: Return a human-readable label for the block.

## Defining Block Properties

Implement `getPageBuilderProperties()` to define editable properties for your block. **Return an array of property objects, not arrays:**

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

### Available Property Types

- `TextProperty` — text input (optionally numeric, min/max)
- `ImageProperty` — image upload/selector
- `CheckboxProperty` — boolean toggle

You can also create your own property types by extending `BlockProperty`.

### Shared Properties

- The base `Block` class provides shared properties for grid size and device visibility (mobile, tablet, desktop). These are automatically included in the builder UI.

## Example: Hero Block

```php
use Trinavo\LivewirePageBuilder\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ImageProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\CheckboxProperty;

class HeroBlock extends Block
{
    public function getPageBuilderIcon()
    {
        return 'heroicon-o-sparkles';
    }

    public function getPageBuilderLabel()
    {
        return 'Hero Section';
    }

    public function getPageBuilderProperties(): array
    {
        return [
            new TextProperty('title', 'Title'),
            new ImageProperty('image', 'Image'),
            new CheckboxProperty('show_button', 'Show Button'),
        ];
    }
}
```

---

See [Extending the Builder](extending-the-builder.md) for advanced block features.
