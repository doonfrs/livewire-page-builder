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

### Property Groups

You can organize related properties into groups using the `setGroup` method:

```php
public function getPageBuilderProperties(): array
{
    return [
        (new TextProperty('title', 'Title'))
            ->setGroup('content', 'Content Settings', 1, 'heroicon-o-document-text'),
        (new TextProperty('subtitle', 'Subtitle'))
            ->setGroup('content', 'Content Settings', 1, 'heroicon-o-document-text'),
        (new ImageProperty('background', 'Background Image'))
            ->setGroup('appearance', 'Appearance', 2, 'heroicon-o-swatch'),
        (new CheckboxProperty('dark_overlay', 'Dark Overlay'))
            ->setGroup('appearance', 'Appearance', 2, 'heroicon-o-swatch'),
    ];
}
```

The `setGroup` method accepts four parameters:

- `group`: The group identifier (string)
- `groupLabel`: The display name for the group (optional)
- `columns`: Number of columns to display properties in (default: 1)
- `groupIcon`: Blade icon name for the group header (optional)

#### Default Group Icons

If not specified, the following default icons will be used for common group names:

- `responsive`: heroicon-o-device-phone-mobile
- `visibility`: heroicon-o-eye
- `appearance`: heroicon-o-swatch
- `content`: heroicon-o-document-text
- `layout`: heroicon-o-rectangle-group
- `animation`: heroicon-o-arrow-path
- Others: heroicon-o-tag

### Available Property Types

- `TextProperty` — text input (optionally numeric, min/max)
- `ImageProperty` — image upload/selector
- `CheckboxProperty` — boolean toggle

You can also create your own property types by extending `BlockProperty`.

### Shared Properties

- The base `Block` class provides shared properties for grid size and device visibility (mobile, tablet, desktop). These are automatically included in the builder UI.
- Responsive properties (mobile, tablet, desktop grid sizes) are grouped in the "Responsive" section.
- Visibility properties (hidden on mobile, tablet, desktop) are grouped in the "Visibility Settings" section.

## Example: Hero Block

```php
use Trinavo\LivewirePageBuilder\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ImageProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\CheckboxProperty;

class HeroBlock extends Block
{
    public function getPageBuilderIcon(): string
    {
        return 'heroicon-o-photo';
    }

    public function getPageBuilderLabel(): string
    {
        return 'Hero Section';
    }

    public function getPageBuilderProperties(): array
    {
        return [
            (new TextProperty('title', 'Title'))
                ->setGroup('content', 'Content Settings', 1, 'heroicon-o-document-text'),
            (new TextProperty('subtitle', 'Subtitle'))
                ->setGroup('content', 'Content Settings', 1, 'heroicon-o-document-text'),
            (new ImageProperty('background', 'Background'))
                ->setGroup('appearance', 'Appearance', 2, 'heroicon-o-swatch'),
            (new CheckboxProperty('overlay', 'Add Dark Overlay'))
                ->setGroup('appearance', 'Appearance', 2, 'heroicon-o-swatch'),
            (new CheckboxProperty('center_text', 'Center Text'))
                ->setGroup('appearance', 'Appearance', 2, 'heroicon-o-swatch'),
        ];
    }

    public function render()
    {
        return view('blocks.hero');
    }
}
```

---

See [Extending the Builder](extending-the-builder.md) for advanced block features.
