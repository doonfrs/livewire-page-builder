# Live Edit

Live edit lets a permitted user change a block's content **from the live page**, without opening the
builder. When it is switched on, every block that opts in shows a small pencil button - the live
edit gear - at its top-left corner, physically left in both directions, so a block only has to
keep one corner clear. Clicking it opens a bottom sheet with the block's live-editable
properties, rendered with the same widgets the builder's property panel uses. Saving writes
into `builder_pages.components` and reloads the page.

It is deliberately narrow. Live edit cannot add, remove, move or restyle blocks, and it cannot
touch row properties or the shared responsive/spacing/style groups. It is a content touch-up tool
for the people who already have editor access, not a second builder.

> **Not a live preview.** Values are buffered, saved, then the page reloads. Blocks derive state in
> `mount()` and their wrapper markup is produced by the parent row, so only a real render shows the
> true result. The reload restores the reader's scroll position.

---

## 1. Declaring what a block exposes

Override `getPageBuilderLiveEditProperties()` on your block. Returning an empty array - the default -
means the block is not live editable and no gear is drawn for it.

The simplest form is a list of property names, resolved against the block's own properties, so
nothing is duplicated and nothing can drift:

```php
use Trinavo\LivewirePageBuilder\Support\Block;

class HeroBlock extends Block
{
    public $title = 'Welcome';
    public $image = null;
    public $internalNote = null;

    public function getPageBuilderProperties(): array
    {
        return [
            new SimpleTextProperty('title', __('Title')),
            new ImageProperty('image', __('Image')),
            new TextProperty('internalNote', __('Internal note')),
        ];
    }

    public function getPageBuilderLiveEditProperties(): array
    {
        return ['title', 'image'];
    }
}
```

You can also return `BlockProperty` objects, exactly like `getPageBuilderProperties()`, when a
property should look different on the live page than it does in the builder:

```php
public function getPageBuilderLiveEditProperties(): array
{
    return [
        new SimpleTextProperty('title', __('Headline')),
        'image',
    ];
}
```

Names that do not resolve to a property are dropped rather than raising an error, so renaming a
property degrades to "that field disappears from the sheet" instead of breaking every page.

Property groups work the same way as in the builder: call `setGroup()` and the sheet renders the
same collapsible sections.

### What can be written

Only the names this method declares. Everything else - other block properties, the shared style
properties, `editMode`, `blockPageName` - is rejected server side even if the browser asks for it.
A `ResponsiveSpacingProperty` expands to its 12 generated per-device keys, as it does everywhere
else in the package.

---

## 2. Turning it on

Live edit is off until the host application enables it, from a service provider, so the decision can
be made per request:

```php
use Illuminate\Support\Facades\Auth;
use Trinavo\LivewirePageBuilder\Services\PageBuilderUIService;

public function boot(): void
{
    app(PageBuilderUIService::class)
        ->enableLiveEdit(fn () => Auth::user()?->can('edit-pages'));
}
```

The closure is resolved on **every** check, never memoised, so a permission change takes effect
immediately and one user's answer is never reused for another.

It also receives the page key and theme id being edited, if you want to scope permission per page -
useful because an embedded header or footer block belongs to its *own* page:

```php
->enableLiveEdit(fn ($pageKey, $themeId) => Auth::user()?->can('edit-page', $pageKey));
```

A zero-argument closure keeps working; the extra arguments are simply ignored.

`enableLiveEdit(true)` / `enableLiveEdit(false)` also work when a static decision is enough.

### Putting the gears behind your own switch

If your app already has an "edit mode" toggle, give the package the Alpine expression to show the
gear behind, and both kinds of affordance appear and disappear together:

```php
app(PageBuilderUIService::class)
    ->enableLiveEdit(fn () => Auth::user()?->can('edit-pages'))
    ->setLiveEditToggleExpression('$store.adminEdit?.on');
```

Leave it unset and the gear is visible whenever live edit is enabled.

---

## 3. Where the gear appears

Anywhere a block is rendered outside edit mode:

- blocks in a page's top-level rows,
- blocks nested inside a `row-block`, at any depth,
- blocks inside an embedded page block (the header/footer pattern) - those save back into their own
  `BuilderPage`, not the page you are looking at.

The gear is drawn in the block's wrapper, not inside the block, so a lazy-loaded block still shows
its gear while its placeholder is on screen. The wrapper gains `relative` for the gear to anchor to,
unless the block already positions itself.

The builder's own canvas never shows live edit gears - it has the property panel.

---

## 4. Things to know before enabling it in production

**Live edit access is editor access.** A `richtext` property stores raw HTML which is rendered
unescaped, so anyone who can live edit a rich text block can inject markup into the page. Grant it to
the same people you would give the builder to.

**Do not serve cached HTML rendered with live edit on.** [Performance](performance-optimization.md)
suggests caching the rendered page; if that cache is filled by a request from a permitted user, the
gear markup is served to everyone. Bypass or vary the cache for live-edit-authorised users, and keep
busting it on `BuilderPageSaved` - which a live edit save fires, exactly like a builder save.

**Rich text needs Quill.** The rich text widget ships Quill via Livewire's `@assets`, so it loads on
the public page on demand. If you published `resources/views/vendor/page-builder/layouts/app.blade.php`
from an older version, remove the four `quill@2.0.3` CDN tags from it or Quill will load twice in the
builder.

**Concurrent saves.** Like the builder's own save, a live edit save rewrites the whole `components`
column. Two people editing the same page at the same moment can overwrite each other.

---

## 5. Custom property widgets

`CustomProperty` widgets work in the sheet as they do in the panel. They keep dispatching the global
`updateBlockProperty` event, which the sheet also listens for.

The built-in widgets address their dispatch at the sheet instead, via a `$updateTarget` property, so
that rows elsewhere on the page are not pulled into every keystroke. If your custom widget is used on
pages with many nested rows, add the same property and pass it through:

```php
use Trinavo\LivewirePageBuilder\Support\Concerns\DispatchesBlockPropertyUpdate;

class MyPropertyWidget extends Component
{
    use DispatchesBlockPropertyUpdate;   // adds public ?string $updateTarget

    public $propertyName;
    public $rowId;
    public $blockId;

    public function updateValue($value): void
    {
        $this->dispatchBlockPropertyUpdate($this->propertyName, $value);
    }
}
```
