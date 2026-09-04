<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Services\PageBuilderRender;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Services\PageBuilderUIService;
use Trinavo\LivewirePageBuilder\Tests\Fixtures\LiveEditableBlock;
use Trinavo\LivewirePageBuilder\Tests\Fixtures\OwnGearBlock;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

/**
 * A block can take the gear over and draw it inside its own markup.
 *
 * The wrapper spans the whole row, so for a block that centres its content inside that
 * width the wrapper's corner is a long way from anything the gear edits. Such a block
 * declares drawsOwnLiveEditGear(), is handed its context, and the wrapper draws none.
 */
class LiveEditOwnGearTest extends TestCase
{
    protected Theme $theme;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        config()->set('page-builder.blocks', [
            LiveEditableBlock::class,
            OwnGearBlock::class,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        PageBuilderService::flushLiveEditCache();

        $service = app(PageBuilderService::class);
        $this->theme = Theme::create(['name' => 'Test Theme', 'description' => 'Test']);

        BuilderPage::create([
            'key' => 'home',
            'theme_id' => $this->theme->id,
            'components' => [
                'row-1' => [
                    'properties' => [],
                    'blocks' => [
                        'block-own' => ['alias' => $service->getClassAlias(OwnGearBlock::class), 'properties' => []],
                        'block-wrapper' => ['alias' => $service->getClassAlias(LiveEditableBlock::class), 'properties' => []],
                    ],
                ],
            ],
        ]);

    }

    protected function renderHome(): string
    {
        $rows = app(PageBuilderRender::class)->parsePage('home', $this->theme->id)['rows'];

        return Blade::render(
            '@foreach ($rows as $row)<x-page-builder::row-view :row="$row" />@endforeach',
            ['rows' => $rows]
        );
    }

    /** @test */
    public function blocks_let_the_wrapper_draw_the_gear_by_default(): void
    {
        $this->assertFalse((new LiveEditableBlock)->drawsOwnLiveEditGear());
        $this->assertTrue((new OwnGearBlock)->drawsOwnLiveEditGear());
    }

    /** @test */
    public function the_service_answers_per_alias(): void
    {
        $service = app(PageBuilderService::class);

        $this->assertTrue($service->blockDrawsOwnLiveEditGear($service->getClassAlias(OwnGearBlock::class)));
        $this->assertFalse($service->blockDrawsOwnLiveEditGear($service->getClassAlias(LiveEditableBlock::class)));
        $this->assertFalse($service->blockDrawsOwnLiveEditGear(null));
        $this->assertFalse($service->blockDrawsOwnLiveEditGear('not-a-block'));
    }

    /**
     * One gear per block, and the one the block draws is in the flow rather than
     * absolutely positioned over the wrapper's corner.
     *
     * @test
     */
    public function the_block_draws_the_only_gear_it_gets(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        $html = $this->renderHome();

        // Both blocks are live editable, so the page carries exactly two gears. Counted
        // by the dispatch, not the event name: the sheet's own component alias contains
        // that string too.
        $this->assertSame(2, substr_count($html, "dispatch('pb-live-edit'"));

        // The block's own one landed in the slot it rendered, not on the wrapper.
        $this->assertStringContainsString('own-gear-slot', $html);
        $this->assertSame(1, substr_count($html, 'inline-flex'));

        // And the wrapper drew exactly one floating gear: the other block's.
        $this->assertSame(1, substr_count($html, 'absolute top-1.5 left-1.5'));
    }

    /**
     * With live edit off, a block that draws its own gear draws nothing: the context it
     * would need is never handed to it.
     *
     * @test
     */
    public function no_context_means_no_gear(): void
    {
        // Live edit left off: the block is never handed a context to draw a gear from.
        $html = $this->renderHome();

        $this->assertStringNotContainsString("dispatch('pb-live-edit'", $html);
        $this->assertStringContainsString('own-gear-slot', $html);
    }
}
