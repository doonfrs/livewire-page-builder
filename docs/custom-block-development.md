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

## Working with Colors

The page builder provides a `ColorData` class to handle color values that can be either Tailwind CSS classes or custom CSS colors (hex, rgb, rgba, hsl, hsla).

### ColorData Class

The `ColorData` class is a value object that parses and provides convenient methods for working with colors in your blocks:

```php
use Trinavo\LivewirePageBuilder\Support\ColorData;

// Parse a color property
$colorData = $this->parseColorProperty('backgroundColor', 'bg-base-200');

// Or parse a color value directly
$colorData = $this->parseColor('#ffffff');
$colorData = $this->parseColor('bg-primary');
$colorData = $this->parseColor('rgba(255, 0, 0, 0.5)');
```

### ColorData Methods

The `ColorData` class provides several helper methods:

#### Type Checking Methods
- `isClass()`: Returns true if the color is a Tailwind class
- `isCss()`: Returns true if the color is a CSS color (hex, rgb, rgba, hsl, hsla)
- `isEmpty()`: Returns true if no color is set

#### Basic Class Helpers
These methods automatically add the appropriate Tailwind prefix and remove any existing prefixes:

- `toBgClass()`: Returns `bg-{color}` for Tailwind classes (e.g., 'primary' → 'bg-primary')
- `toTextClass()`: Returns `text-{color}` for Tailwind classes (e.g., 'primary' → 'text-primary')
- `toBorderClass()`: Returns `border-{color}` for Tailwind classes (e.g., 'primary' → 'border-primary')
- `toDecorationClass()`: Returns `decoration-{color}` for underline colors
- `toShadowClass()`: Returns `shadow-{color}` for shadow colors
- `toRingClass()`: Returns `ring-{color}` for ring/outline colors

#### State Modifier Class Helpers
These methods add both the state prefix (hover:, active:, focus:) and the color type prefix:

- `toHoverBgClass()`: Returns `hover:bg-{color}` (e.g., 'primary' → 'hover:bg-primary')
- `toHoverTextClass()`: Returns `hover:text-{color}`
- `toHoverBorderClass()`: Returns `hover:border-{color}`
- `toActiveBgClass()`: Returns `active:bg-{color}`
- `toActiveTextClass()`: Returns `active:text-{color}`
- `toActiveBorderClass()`: Returns `active:border-{color}`
- `toFocusBgClass()`: Returns `focus:bg-{color}`
- `toFocusTextClass()`: Returns `focus:text-{color}`
- `toFocusBorderClass()`: Returns `focus:border-{color}`
- `toFocusRingClass()`: Returns `focus:ring-{color}`

#### Legacy Methods
- `toClass()`: Returns the raw color value as-is (no prefix added)
- `toInlineStyle(string $property)`: Returns inline CSS style property
- `toStyleAttribute(string $property)`: Returns complete style attribute string
- `toCssVariable()`: Returns raw value for CSS custom properties

### Example Usage in Component

```php
class Header extends Block
{
    public $backgroundColor = null;
    public $hoverTextColor = null;

    public function render()
    {
        return view('livewire.header', [
            // No need to include 'bg-' prefix in default - helper methods add it automatically
            'bgColorData' => $this->parseColorProperty('backgroundColor', 'base-200'),
            'hoverTextData' => $this->parseColorProperty('hoverTextColor', 'primary'),
        ]);
    }
}
```

### Example Usage in View

```blade
<!-- Basic background color example -->
<div class="navbar {{ $bgColorData->toBgClass() }}"
     {!! $bgColorData->toStyleAttribute('background-color') !!}>
    <!-- Content -->
</div>

<!-- Hover state example -->
<a href="#" class="menu-item {{ $hoverTextData->toHoverTextClass() }}">
    Menu Item
</a>

<!-- Multiple color states -->
<button class="btn
    {{ $bgColorData->toBgClass() }}
    {{ $textColorData->toTextClass() }}
    {{ $hoverBgData->toHoverBgClass() }}
    {{ $hoverTextData->toHoverTextClass() }}">
    Click me
</button>

<!-- The helper methods handle prefix logic automatically -->
<!-- 'primary' → 'bg-primary', 'bg-primary' → 'bg-primary' (idempotent) -->
<!-- '#ffffff' → '' (CSS colors return empty string for class helpers) -->
```

### Advanced Example with CSS Variables

For hover states and dynamic styling, you can use CSS variables:

```blade
@php
    $hoverColor = $this->parseColorProperty('hoverColor');
@endphp

<div @if ($hoverColor->isCss()) style="--hover-color: {{ $hoverColor->value }};" @endif>
    <button class="btn {{ $hoverColor->toClass() }}">
        Hover me
    </button>
</div>

@if ($hoverColor->isCss())
    <style>
        .btn:hover {
            color: var(--hover-color) !important;
        }
    </style>
@endif
```

### Benefits

- **Type Safety**: Clear separation between Tailwind classes and CSS colors
- **Cleaner Views**: No need to repeat color type checking logic
- **Flexibility**: Supports both Tailwind classes and custom colors
- **Dark Mode**: Tailwind classes automatically adapt to dark mode

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
