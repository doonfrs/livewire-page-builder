# Custom Block Development

A block is a Livewire 3 component that extends the package's `Block` base class. Once registered in `config/page-builder.php`, it shows up in the editor's block picker and can be dropped into any row.

This guide covers:

- The `Block` base class and its lifecycle hooks
- All property types and their constructor signatures
- Property groups
- Shared responsive properties that every block gets for free
- Working with colors via the `ColorData` helper
- A complete example

---

## 1. The `Block` base class

```php
namespace App\Livewire\Blocks;

use Trinavo\LivewirePageBuilder\Support\Block;

class HeroBlock extends Block
{
    public $title = 'Welcome';
    // ...

    public function render()
    {
        return view('livewire.blocks.hero');
    }
}
```

> ⚠️ The base class lives at **`Trinavo\LivewirePageBuilder\Support\Block`** — not `Trinavo\LivewirePageBuilder\Block`. (Older versions of the README used the wrong namespace.)

Register the class in `config/page-builder.php`:

```php
'blocks' => [
    App\Livewire\Blocks\HeroBlock::class,
],
```

The package will derive a kebab‑case Livewire alias for it (e.g. `page-builder-app-livewire-blocks-hero-block`) and register it with Livewire automatically — you don't need to call `Livewire::component()` yourself.

### Metadata hooks

Override these methods to customise how your block appears in the picker:

| Method | Returns | Used for |
|---|---|---|
| `getPageBuilderLabel(): string` | Display name | Block card title |
| `getPageBuilderCategory(): string` | Category name | Groups blocks in the picker |
| `getPageBuilderIcon(): string` | Blade icon name (e.g. `heroicon-o-photo`) | Block card icon |
| `getPageBuilderProperties(): array` | Array of `BlockProperty` objects | The block's edit panel |

### Rendering

Implement Livewire's `render()` method as usual:

```php
public function render()
{
    return view('livewire.blocks.hero', [
        // pass anything your view needs
    ]);
}
```

The view receives every public property on the component, just like a regular Livewire component.

---

## 2. Defining block properties

`getPageBuilderProperties()` returns an **array of property objects** (not arrays). Each object describes one editable field in the property panel:

```php
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ImageProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\CheckboxProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ColorProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\SelectProperty;

public function getPageBuilderProperties(): array
{
    return [
        new TextProperty('title', __('Title'), false, $this->title),
        new CheckboxProperty('featured', __('Featured')),
        new ImageProperty('image', __('Image')),
        new ColorProperty('accent', __('Accent color')),
        new SelectProperty('alignment', __('Alignment'), [
            'left'   => __('Left'),
            'center' => __('Center'),
            'right'  => __('Right'),
        ]),
    ];
}
```

Every property name must match a `public` property on the component class — that's where the value is stored and how it gets passed to the view.

### Property groups

Group related properties together with `setGroup()`. They render as collapsible sections in the property panel:

```php
return [
    (new TextProperty('title', __('Title')))
        ->setGroup('content', __('Content'), 1, 'heroicon-o-document-text'),

    (new TextProperty('subtitle', __('Subtitle')))
        ->setGroup('content', __('Content'), 1, 'heroicon-o-document-text'),

    (new ImageProperty('background', __('Background')))
        ->setGroup('appearance', __('Appearance'), 2, 'heroicon-o-swatch'),

    (new CheckboxProperty('overlay', __('Dark overlay')))
        ->setGroup('appearance', __('Appearance'), 2, 'heroicon-o-swatch'),
];
```

`setGroup($group, $groupLabel = null, $columns = 1, $groupIcon = null)`:

- `$group` — internal id (string)
- `$groupLabel` — display name (defaults to ucfirst of `$group`)
- `$columns` — how many columns the property grid uses inside the group (1 or 2)
- `$groupIcon` — Blade icon name shown on the group header

When `$groupIcon` is omitted, the package falls back to a default icon based on common group names:

| Group name | Default icon |
|---|---|
| `responsive` | `heroicon-o-device-phone-mobile` |
| `visibility` | `heroicon-o-eye` |
| `appearance` | `heroicon-o-swatch` |
| `content` | `heroicon-o-document-text` |
| `layout` | `heroicon-o-rectangle-group` |
| `animation` | `heroicon-o-arrow-path` |
| anything else | `heroicon-o-tag` |

---

## 3. Property type reference

All property classes live in `Trinavo\LivewirePageBuilder\Support\Properties\`. Each class has both a constructor and a static `make()` builder; the table shows the constructor signature (which is what you usually call).

### `TextProperty`

Single‑line text input. Optionally numeric with min/max.

```php
new TextProperty(
    string $name,
    ?string $label = null,
    bool   $numeric = false,
    mixed  $defaultValue = null,
    ?int   $min = null,
    ?int   $max = null,
)
```

### `SimpleTextProperty`

Plain text input that supports multilingual content (one value per content locale, switchable via the editor's language tabs).

```php
new SimpleTextProperty(
    string $name,
    ?string $label = null,
    mixed  $defaultValue = null,
    bool   $multilingual = true,
)
```

### `RichTextProperty`

Quill‑based WYSIWYG editor. Output is sanitized and class‑normalized so it plays well with Tailwind. Multilingual by default.

```php
new RichTextProperty(
    string $name,
    ?string $label = null,
    mixed  $defaultValue = null,
    bool   $multilingual = true,
)
```

Pass `false` for the last argument if the field shouldn't have language tabs.

### `CheckboxProperty`

Boolean toggle.

```php
new CheckboxProperty(
    string $name,
    ?string $label = null,
    mixed  $defaultValue = null,
)
```

### `SelectProperty`

Dropdown. Pass options as `value => label`.

```php
new SelectProperty(
    string $name,
    ?string $label = null,
    array  $options = [],
    mixed  $defaultValue = null,
)
```

### `ColorProperty`

Color picker. Accepts hex, rgb/rgba, hsl/hsla, oklch/oklab, or DaisyUI semantic names (`'primary'`, `'base-200'`, …).

```php
new ColorProperty(
    string $name,
    ?string $label = null,
    mixed  $defaultValue = null,
)
```

See the [Working with colors](#5-working-with-colors) section for how to consume the saved value.

### `ImageProperty`

Image picker / uploader.

```php
new ImageProperty(
    string $name,
    ?string $label = null,
    mixed  $defaultValue = null,
)
```

### `VideoProperty`

Video URL / embed input.

```php
new VideoProperty(
    string $name,
    ?string $label = null,
    mixed  $defaultValue = null,
)
```

### `IconProperty`

Icon picker. Backed by `blade-heroicons` and `blade-bootstrap-icons` by default; pass `$styles` and `$sets` to restrict the choices.

```php
new IconProperty(
    string $name,
    ?string $label = null,
    ?array $styles = null,   // default: ['outline', 'solid', 'mini', 'regular', 'fill']
    ?array $sets   = null,   // default: ['heroicons', 'bootstrap']
    mixed  $defaultValue = null,
)
```

Because the constructor has positional `null` defaults, this is one of the cases where named arguments read more cleanly:

```php
IconProperty::make(name: 'icon', label: __('Icon'), defaultValue: 'heroicon-o-star');
```

### `FlexibleSizeProperty`

Mobile / tablet / desktop size triplet for width / height‑style values.

```php
new FlexibleSizeProperty(
    string $name,
    ?string $label = null,
    array  $classes = [],          // preset Tailwind classes the user can pick
    bool   $allowCustom = true,    // also allow a custom number + unit
    string $unit = 'px',           // unit when allowCustom is true
    mixed  $defaultValue = null,
)
```

### `ResponsiveSpacingProperty`

A 4‑direction × 3‑breakpoint grid for padding or margin. The component automatically maps to studly‑cased property names — `new ResponsiveSpacingProperty('padding')` writes into `paddingTopMobile`, `paddingTopTablet`, `paddingTopDesktop`, `paddingRightMobile`, etc.

```php
new ResponsiveSpacingProperty(
    string $name,
    ?string $label = null,
    array  $defaultValues = [],   // keyed by 'top' | 'right' | 'bottom' | 'left'
)
```

### `CustomProperty`

Render any Livewire component as a property editor. Use this when the built‑in property types don't fit and you want full control over the UI.

```php
new CustomProperty(
    string $name,
    ?string $label = null,
    ?string $component = null,    // Livewire component alias to render
    ?array $config = null,        // extra props passed into your component
    mixed  $defaultValue = null,
)
```

### `BlockProperty`

The abstract base class. Extend it to define entirely new property types. You must implement `getType()` and `toArray()`.

---

## 4. Shared responsive properties

Every block inherits a large set of shared properties from the `Block` base class. You don't declare or render them — they're injected into the property panel automatically and resolved through Tailwind 4 container queries (`@3xl` ≈ tablet, `@7xl` ≈ desktop) so the preview matches the live render.

| Group | What's covered |
|---|---|
| **Responsive sizing** | width, height, min‑height — per mobile / tablet / desktop |
| **Visibility** | hide on mobile / tablet / desktop; lazy‑load flag |
| **Spacing** | padding & margin top/right/bottom/left — per breakpoint |
| **Typography** | font size (per breakpoint), font weight, text alignment, line height, letter spacing |
| **Color** | text color, background color |
| **Background** | background image (URL, position, size, repeat), gradient (start, end, direction) |
| **Borders** | width per side, color, style, radius per corner |
| **Effects** | box shadow (preset + custom: offset x/y, blur, spread, color, inset), filter, backdrop filter |
| **Transforms** | rotate, scale x/y, translate x/y, skew x/y — per breakpoint |
| **Layout** | flex direction, justify, align, position, z‑index |

These are merged with your block's own properties on the way into the panel. If your block defines a property with the same name as a shared one, your declaration wins.

---

## 5. Working with colors

`ColorProperty` stores either a Tailwind/DaisyUI class fragment (e.g. `'primary'`, `'base-200'`) or a raw CSS color (e.g. `'#ffffff'`, `'rgba(255, 0, 0, 0.5)'`). The `ColorData` value object on the `Block` base class hides that difference.

### Parsing a color in the component

```php
use Trinavo\LivewirePageBuilder\Support\ColorData;

public function render()
{
    return view('livewire.blocks.button', [
        'bg' => $this->parseColorProperty('bgColor', 'primary'),
        'tx' => $this->parseColorProperty('textColor'),
    ]);
}
```

- `parseColorProperty(string $name, mixed $fallback = null): ColorData` — read from `$this->{$name}` with a fallback
- `parseColor(mixed $value): ColorData` — parse a raw value directly

### Using `ColorData` in a view

```blade
<a href="{{ $url }}"
   class="btn {{ $bg->toBgClass() }} {{ $tx->toTextClass() }}"
   {!! $bg->toStyleAttribute('background-color') !!}
   {!! $tx->toStyleAttribute('color') !!}>
    {{ $label }}
</a>
```

The class helpers add the correct Tailwind prefix when the value is a class fragment, and return an empty string when it's a CSS color (so the inline style takes over).

### `ColorData` API

**Type checks** — `isClass()`, `isCss()`, `isEmpty()`

**Class helpers** (return e.g. `bg-primary` for class values, `''` for CSS values):

- `toBgClass()`, `toTextClass()`, `toBorderClass()`
- `toDecorationClass()`, `toShadowClass()`, `toRingClass()`

**State modifiers** (combine state + type prefix):

- `toHoverBgClass()`, `toHoverTextClass()`, `toHoverBorderClass()`
- `toActiveBgClass()`, `toActiveTextClass()`, `toActiveBorderClass()`
- `toFocusBgClass()`, `toFocusTextClass()`, `toFocusBorderClass()`, `toFocusRingClass()`

**Inline style helpers** (return empty for class values):

- `toInlineStyle(string $property)` — returns just `property: value;`
- `toStyleAttribute(string $property)` — returns ` style="property: value;"`

**Raw access**

- `toClass()` — returns the value as‑is, no prefix added
- `toCssVariable()` — for use inside `style="--my-var: …"`
- `->value` — the unparsed value

Using both `toBgClass()` and `toStyleAttribute('background-color')` on the same element is the recommended pattern: whichever matches the stored value will produce output, the other returns empty. The browser sees exactly one of them.

---

## 6. Complete example

```php
namespace App\Livewire\Blocks;

use Trinavo\LivewirePageBuilder\Support\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\CheckboxProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ColorProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ImageProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\RichTextProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;

class HeroBlock extends Block
{
    public $title    = 'Welcome';
    public $subtitle = '';
    public $image    = null;
    public $overlay  = true;
    public $overlayColor = '#000000';
    public $accent   = 'primary';

    public function getPageBuilderLabel(): string    { return __('Hero'); }
    public function getPageBuilderCategory(): string { return __('Content'); }
    public function getPageBuilderIcon(): string     { return 'heroicon-o-photo'; }

    public function getPageBuilderProperties(): array
    {
        return [
            (new TextProperty('title', __('Title'), false, $this->title))
                ->setGroup('content', __('Content'), 1, 'heroicon-o-document-text'),

            (new RichTextProperty('subtitle', __('Subtitle'), $this->subtitle, true))
                ->setGroup('content', __('Content'), 1, 'heroicon-o-document-text'),

            (new ImageProperty('image', __('Background image')))
                ->setGroup('appearance', __('Appearance'), 2, 'heroicon-o-swatch'),

            (new CheckboxProperty('overlay', __('Dark overlay'), true))
                ->setGroup('appearance', __('Appearance'), 2, 'heroicon-o-swatch'),

            (new ColorProperty('overlayColor', __('Overlay color'), '#000000'))
                ->setGroup('appearance', __('Appearance'), 2, 'heroicon-o-swatch'),

            (new ColorProperty('accent', __('Accent color'), 'primary'))
                ->setGroup('appearance', __('Appearance'), 2, 'heroicon-o-swatch'),
        ];
    }

    public function render()
    {
        return view('livewire.blocks.hero', [
            'overlayColor' => $this->parseColorProperty('overlayColor', '#000000'),
            'accent'       => $this->parseColorProperty('accent', 'primary'),
        ]);
    }
}
```

And the view (`resources/views/livewire/blocks/hero.blade.php`):

```blade
<section class="relative">
    @if ($image)
        <img src="{{ $image }}" alt="" class="absolute inset-0 w-full h-full object-cover">
    @endif

    @if ($overlay)
        <div class="absolute inset-0 {{ $overlayColor->toBgClass() }}"
             {!! $overlayColor->toStyleAttribute('background-color') !!}></div>
    @endif

    <div class="relative p-12 text-center">
        <h1 class="text-4xl font-bold {{ $accent->toTextClass() }}"
            {!! $accent->toStyleAttribute('color') !!}>
            {{ $title }}
        </h1>
        <div class="mt-4 prose mx-auto">{!! $subtitle !!}</div>
    </div>
</section>
```

---

## See also

- [Variables](variables.md) — `{{var}}` substitution inside text blocks
- [Multilingual support](multilingual-support.md) — multilingual `RichTextProperty` / `SimpleTextProperty`
- [Advanced configuration](advanced-configuration.md) — middleware, gates, publishing views
