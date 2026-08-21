<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Events\BuilderPageSaved;
use Trinavo\LivewirePageBuilder\Http\Livewire\LiveEdit;
use Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Services\PageBuilderUIService;
use Trinavo\LivewirePageBuilder\Tests\Fixtures\LiveEditableBlock;
use Trinavo\LivewirePageBuilder\Tests\Fixtures\PlainBlock;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class LiveEditSaveTest extends TestCase
{
    protected Theme $theme;

    protected string $liveAlias;

    protected string $plainAlias;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        config()->set('page-builder.blocks', [
            LiveEditableBlock::class,
            PlainBlock::class,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $service = app(PageBuilderService::class);
        $this->liveAlias = $service->getClassAlias(LiveEditableBlock::class);
        $this->plainAlias = $service->getClassAlias(PlainBlock::class);
        $this->theme = Theme::create(['name' => 'Test Theme', 'description' => 'Test']);

        BuilderPage::create([
            'key' => 'header',
            'theme_id' => $this->theme->id,
            'components' => [
                'header-row' => [
                    'properties' => [],
                    'blocks' => [
                        'header-block' => ['alias' => $this->liveAlias, 'properties' => ['title' => 'Header']],
                    ],
                ],
            ],
        ]);

        BuilderPage::create([
            'key' => 'home',
            'theme_id' => $this->theme->id,
            'components' => [
                'row-1' => [
                    'properties' => ['flex' => 'row'],
                    'blocks' => [
                        'block-live' => [
                            'alias' => $this->liveAlias,
                            'properties' => ['title' => 'Top', 'subtitle' => 'Sub', 'internalNote' => 'secret'],
                        ],
                        'block-sibling' => ['alias' => $this->plainAlias, 'properties' => ['heading' => 'Untouched']],
                        'nested-row' => [
                            'alias' => $service->getClassAlias(RowBlock::class),
                            'properties' => ['isNested' => true],
                            'blocks' => [
                                'block-deep' => ['alias' => $this->liveAlias, 'properties' => ['title' => 'Deep']],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        app(PageBuilderUIService::class)->enableLiveEdit(true);
    }

    protected function context(string $page, array $path): array
    {
        return ['page' => $page, 'theme' => $this->theme->id, 'path' => $path];
    }

    protected function components(string $key): array
    {
        return BuilderPage::where('key', $key)->where('theme_id', $this->theme->id)->first()->components;
    }

    /**
     * The sheet's Alpine root is teleported to <body>, so it initialises away from
     * the component it belongs to. `@entangle` bakes the component id into that
     * expression; when Alpine evaluates it before Livewire has registered the
     * component - which happens on any clone pass during a morph - `Livewire.find()`
     * returns undefined and the x-data throws, leaving the sheet and everything
     * after it on the page without an Alpine scope. `$wire.entangle()` resolves
     * lazily through the teleport instead, so keep it that way.
     *
     * @test
     */
    public function the_teleported_sheet_does_not_bake_in_a_component_id(): void
    {
        Livewire::test(LiveEdit::class)
            ->assertDontSee('window.Livewire.find(', escape: false)
            ->assertSee('$wire.entangle(', escape: false);
    }

    /** @test */
    public function opening_loads_only_the_live_edit_properties(): void
    {
        $component = Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context('home', ['row-1', 'block-live']));

        $component->assertSet('open', true)
            ->assertSet('alias', $this->liveAlias)
            ->assertSet('writableKeys', ['title', 'subtitle']);

        $properties = $component->get('properties');
        $this->assertSame(['title' => 'Top', 'subtitle' => 'Sub'], $properties);
        $this->assertArrayNotHasKey('internalNote', $properties);
        $this->assertArrayNotHasKey('desktopWidth', $properties);

        // Only the block's own settings group, none of the shared style groups.
        $this->assertSame(['general'], array_keys($component->get('propertyGroups')));
    }

    /** @test */
    public function opening_does_nothing_while_live_edit_is_disabled(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(false);

        Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context('home', ['row-1', 'block-live']))
            ->assertSet('open', false)
            ->assertSet('properties', [])
            ->assertSet('alias', null);
    }

    /** @test */
    public function opening_does_nothing_for_a_block_that_is_not_live_editable(): void
    {
        Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context('home', ['row-1', 'block-sibling']))
            ->assertSet('open', false)
            ->assertSet('alias', null);
    }

    /** @test */
    public function saving_writes_the_edited_value_and_leaves_siblings_alone(): void
    {
        $before = $this->components('home');

        Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context('home', ['row-1', 'block-live']))
            ->call('updateBlockProperty', null, null, 'title', 'Changed')
            ->call('save')
            ->assertSet('saved', true)
            ->assertSet('open', false);

        $after = $this->components('home');

        $this->assertSame('Changed', $after['row-1']['blocks']['block-live']['properties']['title']);
        $this->assertSame('Sub', $after['row-1']['blocks']['block-live']['properties']['subtitle']);
        $this->assertSame($before['row-1']['blocks']['block-sibling'], $after['row-1']['blocks']['block-sibling']);
        $this->assertSame($before['row-1']['blocks']['nested-row'], $after['row-1']['blocks']['nested-row']);
        $this->assertSame($before['row-1']['properties'], $after['row-1']['properties']);
    }

    /** @test */
    public function saving_reaches_a_block_nested_inside_a_row_block(): void
    {
        Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context('home', ['row-1', 'nested-row', 'block-deep']))
            ->call('updateBlockProperty', null, null, 'title', 'Deep changed')
            ->call('save');

        $after = $this->components('home');

        $this->assertSame(
            'Deep changed',
            $after['row-1']['blocks']['nested-row']['blocks']['block-deep']['properties']['title']
        );
        $this->assertSame('Top', $after['row-1']['blocks']['block-live']['properties']['title']);
    }

    /** @test */
    public function saving_an_embedded_page_block_writes_to_that_page_only(): void
    {
        $homeBefore = $this->components('home');

        Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context('header', ['header-row', 'header-block']))
            ->call('updateBlockProperty', null, null, 'title', 'New header')
            ->call('save');

        $this->assertSame(
            'New header',
            $this->components('header')['header-row']['blocks']['header-block']['properties']['title']
        );
        $this->assertSame($homeBefore, $this->components('home'), 'the host page must not be touched');
    }

    /** @test */
    public function properties_outside_the_declaration_are_never_written(): void
    {
        Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context('home', ['row-1', 'block-live']))
            // Straight from the browser: a property the block never declared live editable,
            // and one that would change how the block is rendered entirely.
            ->set('properties.internalNote', 'tampered')
            ->set('properties.editMode', true)
            ->set('properties.title', 'Legitimate')
            ->call('save');

        $stored = $this->components('home')['row-1']['blocks']['block-live']['properties'];

        $this->assertSame('Legitimate', $stored['title']);
        $this->assertSame('secret', $stored['internalNote']);
        $this->assertArrayNotHasKey('editMode', $stored);
    }

    /** @test */
    public function buffering_ignores_property_names_outside_the_declaration(): void
    {
        $component = Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context('home', ['row-1', 'block-live']))
            ->call('updateBlockProperty', null, null, 'internalNote', 'tampered');

        $this->assertArrayNotHasKey('internalNote', $component->get('properties'));
    }

    /** @test */
    public function saving_is_refused_once_live_edit_is_switched_off(): void
    {
        $component = Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context('home', ['row-1', 'block-live']))
            ->call('updateBlockProperty', null, null, 'title', 'Changed');

        // The gear is just markup; permission has to be re-checked when the write happens.
        app(PageBuilderUIService::class)->enableLiveEdit(false);

        $component->call('save')->assertSet('saved', false);

        $this->assertSame('Top', $this->components('home')['row-1']['blocks']['block-live']['properties']['title']);
    }

    /** @test */
    public function a_forged_path_writes_nothing_and_does_not_throw(): void
    {
        $before = $this->components('home');

        Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context('home', ['row-1', 'no-such-block']))
            ->assertSet('alias', null)
            ->call('save');

        $this->assertSame($before, $this->components('home'));
    }

    /** @test */
    public function an_unknown_page_key_creates_nothing(): void
    {
        Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context('not-a-page', ['row-1', 'block-live']))
            ->assertSet('open', true)
            ->assertSet('alias', null)
            ->call('save');

        $this->assertDatabaseMissing('builder_pages', ['key' => 'not-a-page']);
    }

    /** @test */
    public function a_malformed_context_is_rejected(): void
    {
        foreach ([[], ['page' => 'home'], ['page' => '', 'path' => ['x']], ['page' => 'home', 'path' => []]] as $ctx) {
            Livewire::test(LiveEdit::class)
                ->call('openBlock', $ctx)
                ->assertSet('open', false);
        }
    }

    /** @test */
    public function saving_fires_the_page_saved_event_so_hosts_can_bust_their_cache(): void
    {
        Event::fake([BuilderPageSaved::class]);

        Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context('home', ['row-1', 'block-live']))
            ->call('updateBlockProperty', null, null, 'title', 'Changed')
            ->call('save');

        Event::assertDispatched(BuilderPageSaved::class);
    }

    /** @test */
    public function saving_reloads_the_page_because_blocks_derive_state_at_mount(): void
    {
        $component = Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context('home', ['row-1', 'block-live']))
            ->call('updateBlockProperty', null, null, 'title', 'Changed')
            ->call('save');

        // Pushing the new values onto the already-mounted block would leave whatever it
        // derived in mount() stale, so the sheet reloads instead - and puts the reader
        // back where they were.
        $js = json_encode($component->effects['xjs'] ?? []);

        $this->assertStringContainsString('window.location.reload()', $js);
        $this->assertStringContainsString('pbLiveEditScroll', $js);
    }
}
