<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Services\PageBuilderUIService;
use Trinavo\LivewirePageBuilder\Support\Concerns\OrganizesBlockProperties;

/**
 * The live edit property sheet: one instance per public page, opened by a block's gear.
 *
 * Everything it is handed comes from the browser, so nothing here trusts the payload.
 * Authorisation is re-checked on open and again on save, the block's identity is read
 * back out of the database rather than taken from the client, and only the property
 * names the block itself declared live editable are ever written.
 */
class LiveEdit extends Component
{
    use OrganizesBlockProperties;
    use WithFileUploads;

    /**
     * Livewire alias of this component, used to address property updates at it.
     */
    public const ALIAS = 'page-builder-live-edit';

    public bool $open = false;

    /**
     * ['page' => pageKey, 'theme' => themeId, 'path' => [rowId, blockId, ...]]
     */
    #[Locked]
    public ?array $context = null;

    /**
     * The block alias as stored in the database, never as supplied by the browser.
     */
    #[Locked]
    public ?string $alias = null;

    #[Locked]
    public ?string $blockLabel = null;

    /**
     * Property names this sheet is allowed to write, from the block's own declaration.
     *
     * @var array<int, string>
     */
    #[Locked]
    public array $writableKeys = [];

    /**
     * Current (unsaved) values, keyed by property name.
     */
    public array $properties = [];

    /**
     * The values the block had when the sheet opened, so Cancel can put them back.
     *
     * @var array<string, mixed>
     */
    #[Locked]
    public array $originalProperties = [];

    /**
     * Writable keys that are also real public properties on the block, so they can be
     * pushed into the live component for preview.
     *
     * A CustomProperty is usually virtual - its widget writes elsewhere and the block has
     * no property of that name - and setting one on the component would just error.
     *
     * @var array<int, string>
     */
    #[Locked]
    public array $previewableKeys = [];

    /**
     * BlockProperty::toArray() schemas for the live editable properties.
     */
    public array $blockProperties = [];

    public array $propertyGroups = [];

    public bool $saved = false;

    public ?string $error = null;

    public function render()
    {
        return view('page-builder::livewire.live-edit');
    }

    /**
     * Open the sheet for the block a gear points at.
     */
    #[On('pb-live-edit')]
    public function openBlock(array $ctx): void
    {
        $this->reset(['context', 'alias', 'blockLabel', 'writableKeys', 'properties', 'originalProperties', 'previewableKeys', 'blockProperties', 'propertyGroups', 'saved', 'error']);

        $context = $this->normalizeContext($ctx);

        if (! $context || ! $this->allowed($context)) {
            return;
        }

        $node = $this->findNode($context);

        if ($node === null) {
            $this->error = __('This block is no longer available.');
            $this->open = true;

            return;
        }

        $block = app(PageBuilderService::class)->makeBlockForAlias($node['alias']);

        if (! $block || ! $block->hasLiveEditProperties()) {
            return;
        }

        $this->context = $context;
        $this->alias = $node['alias'];
        $this->blockLabel = $block->getPageBuilderLabel();
        $this->writableKeys = $block->getLiveEditPropertyKeys();

        $this->blockProperties = array_map(
            fn ($property) => $property->toArray(),
            $block->resolveLiveEditProperties()
        );

        // Stored values win, block defaults fill the gaps, and nothing outside the
        // block's own live edit declaration is ever loaded into the form.
        $stored = $node['properties'] ?? [];
        $defaults = $block->getPropertyValues();
        $values = [];
        foreach ($this->writableKeys as $key) {
            $values[$key] = $stored[$key] ?? $defaults[$key] ?? null;
        }
        $this->properties = $values;
        $this->originalProperties = $values;

        // Only names the block actually declares as public properties can be previewed;
        // virtual CustomProperty names would throw when set on the component.
        $this->previewableKeys = array_values(array_filter(
            $this->writableKeys,
            fn (string $key) => property_exists($block, $key)
        ));

        $this->propertyGroups = $this->organizeProperties($this->blockProperties);
        $this->open = true;
    }

    /**
     * Buffer a property change.
     *
     * Reached three ways: directly from the inline text/checkbox inputs in the sheet,
     * from a widget dispatch addressed at this component, and from an untargeted
     * dispatch by a host-supplied custom property widget. Row and block ids are
     * ignored - this sheet only ever edits the block it was opened for.
     *
     * @param  mixed  $value
     */
    #[On('updateBlockProperty')]
    public function updateBlockProperty($rowId, $blockId, $propertyName, $value): void
    {
        if (! $this->open || ! in_array($propertyName, $this->writableKeys, true)) {
            return;
        }

        $this->properties[$propertyName] = $this->sanitizeValue($value);
        $this->saved = false;

        $this->pushPreview([$propertyName => $this->properties[$propertyName]]);
    }

    /**
     * Push values into the live block so the page shows them straight away.
     *
     * The block on the page is its own Livewire component; the sheet cannot reach into
     * it from PHP, so the browser is told which element to find and what to set on it.
     * Livewire re-renders that component alone - nothing else on the page moves.
     *
     * @param  array<string, mixed>  $props
     */
    protected function pushPreview(array $props): void
    {
        if (! $this->context) {
            return;
        }

        $previewable = array_intersect_key($props, array_flip($this->previewableKeys));

        if ($previewable === []) {
            return;
        }

        $this->dispatch(
            'pb-live-preview',
            target: self::domId($this->context),
            props: $previewable
        );
    }

    /**
     * The DOM id of a block's wrapper on the page.
     *
     * Derived from the context rather than the block id alone: block ids are only unique
     * within their own page, and one page can be embedded more than once.
     */
    public static function domId(array $context): string
    {
        $signature = implode('|', [
            (string) ($context['page'] ?? ''),
            (string) ($context['theme'] ?? ''),
            implode('/', $context['path'] ?? []),
        ]);

        return 'pb-live-'.Str::substr(sha1($signature), 0, 12);
    }

    /**
     * Persist the buffered values into the page this block belongs to.
     */
    public function save(): void
    {
        if (! $this->context || ! $this->allowed($this->context)) {
            return;
        }

        $page = $this->findPage($this->context);

        if (! $page) {
            $this->error = __('This block is no longer available.');

            return;
        }

        // The json cast hands back a fresh array on every read, so the tree is walked on
        // a local copy and the whole column is written back.
        $components = $page->components ?? [];
        $path = $this->context['path'];
        $rowId = array_shift($path);

        if (! isset($components[$rowId])) {
            $this->error = __('This block is no longer available.');

            return;
        }

        $node = &$components[$rowId];

        foreach ($path as $segment) {
            if (! isset($node['blocks'][$segment])) {
                $this->error = __('This block is no longer available.');

                return;
            }

            $node = &$node['blocks'][$segment];
        }

        if (($node['alias'] ?? null) !== $this->alias) {
            $this->error = __('This block is no longer available.');

            return;
        }

        $changed = [];

        foreach ($this->writableKeys as $key) {
            if (! array_key_exists($key, $this->properties)) {
                continue;
            }

            $value = $this->sanitizeValue($this->properties[$key]);

            if (($this->originalProperties[$key] ?? null) !== $value) {
                $changed[] = $key;
            }

            $node['properties'][$key] = $value;
        }
        unset($node);

        $page->components = $components;
        $page->saveOrFail();

        $this->originalProperties = $this->properties;
        $this->saved = true;
        $this->open = false;

        // The page already shows these values: every change was previewed into the live
        // block as it was made. The one thing preview cannot repaint is the block's
        // wrapper, which the parent row renders from the shared style properties - so a
        // reload is needed only when one of those actually changed.
        if ($this->wrapperAffectedBy($changed)) {
            $this->js(<<<'JS'
                sessionStorage.setItem('pbLiveEditScroll', String(window.scrollY));
                window.location.reload();
            JS);
        }
    }

    /**
     * Do any of these keys feed the wrapper the parent row draws around the block?
     *
     * @param  array<int, string>  $keys
     */
    protected function wrapperAffectedBy(array $keys): bool
    {
        if ($keys === []) {
            return false;
        }

        $block = app(PageBuilderService::class)->makeBlockForAlias((string) $this->alias);

        return $block !== null && array_intersect($keys, $block->getSharedPropertyKeys()) !== [];
    }

    /**
     * Cancel: put the page back exactly as it was before the sheet opened.
     */
    public function close(): void
    {
        $reverted = [];

        foreach ($this->properties as $key => $value) {
            $original = $this->originalProperties[$key] ?? null;

            if ($original !== $value) {
                $reverted[$key] = $original;
            }
        }

        $this->pushPreview($reverted);

        $this->properties = $this->originalProperties;
        $this->open = false;
    }

    /**
     * Is live edit available to the current user for this page?
     */
    protected function allowed(array $context): bool
    {
        return app(PageBuilderUIService::class)
            ->isLiveEditEnabled($context['page'], $context['theme']);
    }

    /**
     * Coerce a browser-supplied context into the shape the rest of this class assumes.
     */
    protected function normalizeContext(array $ctx): ?array
    {
        $page = $ctx['page'] ?? null;
        $path = $ctx['path'] ?? null;

        if (! is_string($page) || $page === '' || ! is_array($path) || $path === []) {
            return null;
        }

        $segments = [];
        foreach ($path as $segment) {
            if (! is_string($segment) && ! is_int($segment)) {
                return null;
            }
            $segments[] = (string) $segment;
        }

        $theme = $ctx['theme'] ?? null;

        return [
            'page' => $page,
            'theme' => is_numeric($theme) ? (int) $theme : null,
            'path' => $segments,
        ];
    }

    protected function findPage(array $context): ?BuilderPage
    {
        return BuilderPage::where('key', $context['page'])
            ->where('theme_id', $context['theme'])
            ->first();
    }

    /**
     * Walk the stored component tree to the addressed block, or null if it is not there.
     */
    protected function findNode(array $context): ?array
    {
        $page = $this->findPage($context);

        if (! $page) {
            return null;
        }

        $path = $context['path'];
        $node = $page->components[array_shift($path)] ?? null;

        foreach ($path as $segment) {
            $node = $node['blocks'][$segment] ?? null;

            if (! is_array($node)) {
                return null;
            }
        }

        return isset($node['alias']) ? $node : null;
    }

    /**
     * Accept scalars and the multilingual content structure; reject anything else.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function sanitizeValue($value)
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if (! is_array($value) || ! ($value['multilingual'] ?? false)) {
            return null;
        }

        $locales = array_keys(pb_content_locales());
        $values = [];

        foreach ($value['values'] ?? [] as $locale => $localeValue) {
            if (in_array($locale, $locales, true) && (is_scalar($localeValue) || $localeValue === null)) {
                $values[$locale] = $localeValue;
            }
        }

        $default = $value['default_locale'] ?? null;

        return [
            'multilingual' => true,
            'values' => $values,
            'default_locale' => in_array($default, $locales, true) ? $default : pb_default_content_locale(),
        ];
    }

    /**
     * Stable, collision-free key prefix for the sheet's field components.
     *
     * Block ids are only unique within their own page, and the same page can be embedded
     * more than once, so the whole path is hashed instead.
     */
    public function fieldKey(): string
    {
        return Str::substr(md5(json_encode($this->context)), 0, 12);
    }
}
