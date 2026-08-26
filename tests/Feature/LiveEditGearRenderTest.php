<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Trinavo\LivewirePageBuilder\Http\Livewire\LiveEdit;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Services\PageBuilderRender;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Services\PageBuilderUIService;
use Trinavo\LivewirePageBuilder\Tests\Fixtures\LiveEditableBlock;
use Trinavo\LivewirePageBuilder\Tests\Fixtures\PlainBlock;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class LiveEditGearRenderTest extends TestCase
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
            'key' => 'home',
            'theme_id' => $this->theme->id,
            'components' => [
                'row-1' => [
                    'properties' => [],
                    'blocks' => [
                        'block-live' => ['alias' => $this->liveAlias, 'properties' => ['title' => 'Top']],
                        'block-plain' => ['alias' => $this->plainAlias, 'properties' => []],
                    ],
                ],
            ],
        ]);
    }

    protected function renderHome(int $times = 1): string
    {
        $rows = app(PageBuilderRender::class)->parsePage('home', $this->theme->id)['rows'];

        $template = str_repeat('@foreach ($rows as $row)<x-page-builder::row-view :row="$row" />@endforeach', $times);

        return Blade::render($template, ['rows' => $rows]);
    }

    /**
     * Pull the contexts back out of the rendered gears.
     *
     * Blade's @js() emits JSON.parse('...') with quotes escaped as JS \uXXXX sequences,
     * so they have to be unescaped before the payload is JSON again.
     */
    protected function gearContexts(string $html): array
    {
        preg_match_all("/pb-live-edit', \{ ctx: JSON\.parse\('(.*?)'\) \}\)/", $html, $matches);

        return array_map(function (string $raw) {
            $json = preg_replace_callback(
                '/\\\\u([0-9a-fA-F]{4})/',
                fn ($m) => mb_chr(hexdec($m[1])),
                $raw
            );

            return json_decode($json, true);
        }, $matches[1]);
    }

    /**
     * How many property sheets are mounted. Each one names itself in its Livewire snapshot.
     */
    protected function mountedSheetCount(string $html): int
    {
        return substr_count($html, LiveEdit::ALIAS);
    }

    /** @test */
    public function no_gear_and_no_sheet_are_rendered_while_live_edit_is_off(): void
    {
        $html = $this->renderHome();

        $this->assertSame([], $this->gearContexts($html));
        $this->assertSame(0, $this->mountedSheetCount($html));
    }

    /** @test */
    public function a_gear_is_rendered_only_for_blocks_that_declare_live_edit_properties(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        $contexts = $this->gearContexts($this->renderHome());

        // One gear for block-live, none for block-plain.
        $this->assertCount(1, $contexts);
        $this->assertSame(['row-1', 'block-live'], $contexts[0]['path']);
    }

    /** @test */
    public function the_gear_carries_the_page_theme_and_path_of_its_block(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        $contexts = $this->gearContexts($this->renderHome());

        $this->assertSame([
            'page' => 'home',
            'theme' => $this->theme->id,
            'path' => ['row-1', 'block-live'],
        ], $contexts[0]);
    }

    /** @test */
    public function the_gear_is_anchored_physically_left_in_both_directions(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        $html = $this->renderHome();

        // `left-1.5`, not `start-1.5`: the corner the gear occupies must not move under RTL,
        // so a block only ever has to keep one corner clear of its own controls.
        $this->assertStringContainsString('top-1.5 left-1.5', $html);
        $this->assertStringNotContainsString('top-1.5 start-1.5', $html);
    }

    /** @test */
    public function the_gear_stays_below_the_overlay_band(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        $html = $this->renderHome();

        // Modals, drawers and menus live at z-50 and up. A gear above that band floats
        // over an open modal, which is worse than being covered by one.
        $this->assertStringContainsString('left-1.5 z-40', $html);
        $this->assertStringNotContainsString('z-[60]', $html);
    }

    /** @test */
    public function the_gears_wrapper_becomes_a_containing_block(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        $rows = app(PageBuilderRender::class)->parsePage('home', $this->theme->id)['rows'];

        // No position of its own, so the wrapper gains `relative` for the gear to anchor to.
        $this->assertStringNotContainsString('relative', $rows['row-1']['blocks']['block-live']['cssClasses']);
        $this->assertStringContainsString(
            'relative',
            Blade::render('<x-page-builder::row-view :row="$row" />', ['row' => $rows['row-1']])
        );
    }

    /** @test */
    public function a_block_that_positions_itself_is_left_alone(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        $page = BuilderPage::where('key', 'home')->first();
        $components = $page->components;
        $components['row-1']['blocks']['block-live']['properties']['position'] = 'absolute';
        $page->components = $components;
        $page->save();

        $rows = app(PageBuilderRender::class)->parsePage('home', $this->theme->id)['rows'];
        $html = Blade::render('<x-page-builder::row-view :row="$row" />', ['row' => $rows['row-1']]);

        // absolute already establishes a containing block; adding relative would just
        // leave two competing position utilities on the same element.
        $this->assertMatchesRegularExpression('/class="[^"]*absolute[^"]*"/', $html);
        $this->assertDoesNotMatchRegularExpression('/class="[^"]*absolute[^"]*\brelative\b[^"]*"/', $html);
    }

    /** @test */
    public function only_one_property_sheet_is_mounted_however_many_times_the_page_is_rendered(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        // A host typically renders header, content and footer as separate page views.
        $html = $this->renderHome(times: 3);

        $this->assertCount(3, $this->gearContexts($html), 'every block should still get its gear');
        $this->assertSame(1, $this->mountedSheetCount($html));
    }

    /** @test */
    public function the_gear_can_be_put_behind_a_hosts_own_edit_mode_switch(): void
    {
        app(PageBuilderUIService::class)
            ->enableLiveEdit(true)
            ->setLiveEditToggleExpression('$store.adminEdit?.on');

        $this->assertStringContainsString('x-show="$store.adminEdit?.on"', $this->renderHome());
    }
}
