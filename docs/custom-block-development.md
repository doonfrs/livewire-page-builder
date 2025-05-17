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
use Trinavo\LivewirePageBuilder\Support\Properties\ColorProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\SelectProperty;

public function getPageBuilderProperties(): array
{
    return [
        new CheckboxProperty('featured', 'Featured'),
        new TextProperty('title', 'Title'),
        new ImageProperty('image', 'Image'),
        new ColorProperty('accent_color', 'Accent Color'),
        new SelectProperty('alignment', 'Text Alignment', [
            'left' => 'Left',
            'center' => 'Center', 
            'right' => 'Right'
        ]),
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

- `TextProperty` — text input with optional parameters:
  - `numeric` (boolean): Whether the input accepts numbers only
  - `min` (integer): Minimum value (for numeric inputs)
  - `max` (integer): Maximum value (for numeric inputs)
  - `defaultValue`: Default value for the property

- `RichTextProperty` — rich text editor with formatting options (uses Quill, Trix, or TipTap; stores HTML)
  - `defaultValue`: Default HTML content for the editor

- `ImageProperty` — image upload/selector with:
  - `defaultValue`: Default image URL

- `CheckboxProperty` — boolean toggle with:
  - `defaultValue`: Default checked state (true/false)

- `ColorProperty` — color picker with:
  - `defaultValue`: Default color value

- `SelectProperty` — dropdown selector with:
  - `options` (array): Key-value pairs for dropdown options
  - `defaultValue`: Default selected option key

You can also create your own property types by extending `BlockProperty`.

### Shared Properties

The base `Block` class automatically provides the following shared properties:

#### Responsive Width Properties

- Mobile, tablet and desktop width settings with options like full width, auto, 1/2, 1/3, etc.

#### Visibility Settings

- Show/hide options for mobile, tablet, and desktop views

#### Spacing Properties

- Padding (top, right, bottom, left)
- Margin (top, right, bottom, left)

#### Style Properties

- Text color
- Background color

#### Background Image Properties

- Image upload
- Position (center, top, right, bottom, left, etc.)
- Size (cover, contain, auto, 100%)
- Repeat (no-repeat, repeat, repeat-x, repeat-y)

## Example: Hero Block

```php
use Trinavo\LivewirePageBuilder\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ImageProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\CheckboxProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ColorProperty;

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
            (new ColorProperty('overlay_color', 'Overlay Color', defaultValue: '#000000'))
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
