<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\BuilderPageBlock;
use Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Services\PageBuilderUIService;
use Trinavo\LivewirePageBuilder\Tests\Fixtures\LiveEditableBlock;
use Trinavo\LivewirePageBuilder\Tests\Fixtures\PlainBlock;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

/**
 * Blocks are rendered by three different views. This covers the two that go through a
 * Livewire component rather than the public row-view blade.
 */
class LiveEditNestedRenderTest extends TestCase
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
                        'header-plain' => ['alias' => $this->plainAlias, 'properties' => []],
                    ],
                ],
            ],
        ]);
    }

    protected function gearContexts(string $html): array
    {
        preg_match_all("/pb-live-edit', \{ ctx: JSON\.parse\('(.*?)'\) \}\)/", $html, $matches);

        return array_map(function (string $raw) {
            $json = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', fn ($m) => mb_chr(hexdec($m[1])), $raw);

            return json_decode($json, true);
        }, $matches[1]);
    }

    protected function nestedRowBlocks(): array
    {
        return [
            'block-deep' => ['alias' => $this->liveAlias, 'properties' => ['title' => 'Deep']],
            'block-deep-plain' => ['alias' => $this->plainAlias, 'properties' => []],
        ];
    }

    /** @test */
    public function a_nested_row_extends_the_path_it_was_handed(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        $html = Livewire::test(RowBlock::class, [
            'blocks' => $this->nestedRowBlocks(),
            'rowId' => 'nested-row',
            'properties' => [],
            'isNested' => true,
            'editMode' => false,
            'liveEditContext' => ['page' => 'home', 'theme' => $this->theme->id, 'path' => ['row-1', 'nested-row']],
        ])->html();

        $contexts = $this->gearContexts($html);

        $this->assertCount(1, $contexts, 'only the live editable child gets a gear');
        $this->assertSame(['row-1', 'nested-row', 'block-deep'], $contexts[0]['path']);
        $this->assertSame('home', $contexts[0]['page']);
    }

    /** @test */
    public function a_nested_row_renders_no_gears_without_a_context(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        $html = Livewire::test(RowBlock::class, [
            'blocks' => $this->nestedRowBlocks(),
            'rowId' => 'nested-row',
            'properties' => [],
            'isNested' => true,
            'editMode' => false,
        ])->html();

        $this->assertSame([], $this->gearContexts($html));
    }

    /** @test */
    public function the_builder_editor_canvas_never_shows_live_edit_gears(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        $html = Livewire::test(RowBlock::class, [
            'blocks' => $this->nestedRowBlocks(),
            'rowId' => 'nested-row',
            'properties' => [],
            'isNested' => true,
            'editMode' => true,
            'liveEditContext' => ['page' => 'home', 'theme' => $this->theme->id, 'path' => ['row-1', 'nested-row']],
        ])->html();

        // Edit mode has its own property panel; a second one would just be confusing.
        $this->assertSame([], $this->gearContexts($html));
    }

    /** @test */
    public function an_embedded_page_block_gears_point_at_its_own_page(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        $html = Livewire::test(BuilderPageBlock::class, ['blockPageName' => 'header'])->html();

        $contexts = $this->gearContexts($html);

        $this->assertCount(1, $contexts);
        $this->assertSame([
            'page' => 'header',
            'theme' => $this->theme->id,
            'path' => ['header-row', 'header-block'],
        ], $contexts[0]);
    }

    /** @test */
    public function an_embedded_page_block_renders_without_rows_when_the_page_is_missing(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        // mount() bails early here, so $rows stays null - it must not blow up on dehydrate.
        Livewire::test(BuilderPageBlock::class, ['blockPageName' => 'no-such-page'])
            ->assertSet('rows', null)
            ->assertSet('page', null);
    }

    /** @test */
    public function a_row_ignores_property_updates_meant_for_someone_else(): void
    {
        $component = Livewire::test(RowBlock::class, [
            'blocks' => $this->nestedRowBlocks(),
            'rowId' => 'nested-row',
            'properties' => [],
            'isNested' => true,
            'editMode' => false,
        ]);

        $before = $component->get('blocks');

        // updateBlockProperty is a global event, so every row on a public page hears the
        // live edit sheet's traffic. Unmatched ids must be a cheap no-op.
        $component->call('updateBlockProperty', null, 'someone-elses-block', 'title', 'Changed');

        $this->assertSame($before, $component->get('blocks'));
    }
}
