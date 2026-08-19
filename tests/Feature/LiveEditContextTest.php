<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Services\PageBuilderRender;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Services\PageBuilderUIService;
use Trinavo\LivewirePageBuilder\Tests\Fixtures\LiveEditableBlock;
use Trinavo\LivewirePageBuilder\Tests\Fixtures\PlainBlock;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

/**
 * The live edit "context" is how a gear on the public page names the block it edits:
 * which page, which theme, and the path of ids down builder_pages.components.
 */
class LiveEditContextTest extends TestCase
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
                    'properties' => [],
                    'blocks' => [
                        'block-live' => ['alias' => $this->liveAlias, 'properties' => ['title' => 'Top']],
                        'block-plain' => ['alias' => $this->plainAlias, 'properties' => []],
                        'nested-row' => [
                            'alias' => $service->getClassAlias(RowBlock::class),
                            'properties' => ['isNested' => true],
                            'blocks' => [
                                'block-deep' => ['alias' => $this->liveAlias, 'properties' => ['title' => 'Deep']],
                            ],
                        ],
                        'block-page' => [
                            'alias' => 'builder-page-block',
                            'properties' => ['blockPageName' => 'header'],
                        ],
                    ],
                ],
            ],
        ]);
    }

    protected function enableLiveEdit(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);
    }

    protected function parseHome(): array
    {
        return app(PageBuilderRender::class)->parsePage('home', $this->theme->id);
    }

    /** @test */
    public function nothing_is_stamped_while_live_edit_is_off(): void
    {
        $blocks = $this->parseHome()['rows']['row-1']['blocks'];

        $this->assertNull($blocks['block-live']['liveEditContext']);
        $this->assertFalse($blocks['block-live']['liveEditable']);
        $this->assertNull($blocks['block-page']['rows']['header-row']['blocks']['header-block']['liveEditContext']);
    }

    /** @test */
    public function a_top_level_block_is_addressed_by_row_then_block(): void
    {
        $this->enableLiveEdit();

        $block = $this->parseHome()['rows']['row-1']['blocks']['block-live'];

        $this->assertTrue($block['liveEditable']);
        $this->assertSame([
            'page' => 'home',
            'theme' => $this->theme->id,
            'path' => ['row-1', 'block-live'],
        ], $block['liveEditContext']);
    }

    /** @test */
    public function blocks_without_live_edit_properties_get_a_context_but_no_gear(): void
    {
        $this->enableLiveEdit();

        $block = $this->parseHome()['rows']['row-1']['blocks']['block-plain'];

        $this->assertFalse($block['liveEditable']);
        $this->assertNotNull($block['liveEditContext'], 'the path is still needed to reach nested children');
    }

    /** @test */
    public function a_nested_block_path_grows_by_one_id_per_level(): void
    {
        $this->enableLiveEdit();

        $deep = $this->parseHome()['rows']['row-1']['blocks']['nested-row']['blocks']['block-deep'];

        $this->assertTrue($deep['liveEditable']);
        $this->assertSame(['row-1', 'nested-row', 'block-deep'], $deep['liveEditContext']['path']);
    }

    /** @test */
    public function an_embedded_page_block_restarts_the_context_on_its_own_page(): void
    {
        $this->enableLiveEdit();

        $embedded = $this->parseHome()['rows']['row-1']['blocks']['block-page']['rows']['header-row']['blocks']['header-block'];

        // The header's blocks live in the header BuilderPage row, so the path must start
        // there rather than continue down the home page's tree.
        $this->assertSame([
            'page' => 'header',
            'theme' => $this->theme->id,
            'path' => ['header-row', 'header-block'],
        ], $embedded['liveEditContext']);
    }

    /** @test */
    public function parse_page_reports_the_theme_it_resolved(): void
    {
        $this->assertSame($this->theme->id, $this->parseHome()['themeId']);
    }

    /** @test */
    public function the_permission_closure_is_asked_about_the_embedded_page_too(): void
    {
        $seen = [];
        app(PageBuilderUIService::class)->enableLiveEdit(function ($pageKey) use (&$seen) {
            $seen[] = $pageKey;

            return $pageKey !== 'header';
        });

        $blocks = $this->parseHome()['rows']['row-1']['blocks'];

        $this->assertContains('home', $seen);
        $this->assertContains('header', $seen);

        // Denying just the header leaves the host page editable and the header alone.
        $this->assertNotNull($blocks['block-live']['liveEditContext']);
        $this->assertNull($blocks['block-page']['rows']['header-row']['blocks']['header-block']['liveEditContext']);
    }
}
