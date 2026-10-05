<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Services\PageBuilderRender;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Services\PageBuilderUIService;
use Trinavo\LivewirePageBuilder\Tests\Fixtures\HiddenContentBlock;
use Trinavo\LivewirePageBuilder\Tests\Fixtures\LiveEditableBlock;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

/**
 * A block that shows nothing must not leave its gear floating over blank space.
 *
 * The gear is drawn in the wrapper before the block renders, so the block answers
 * hasVisibleContent() afterwards and its root is stamped data-pb-hidden. The live edit
 * mount carries the CSS that hides any data-pb-edit-gear tied to that root.
 */
class LiveEditHiddenContentTest extends TestCase
{
    protected Theme $theme;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        config()->set('page-builder.blocks', [
            LiveEditableBlock::class,
            HiddenContentBlock::class,
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
                        'block-empty' => ['alias' => $service->getClassAlias(HiddenContentBlock::class), 'properties' => []],
                        'block-live' => ['alias' => $service->getClassAlias(LiveEditableBlock::class), 'properties' => []],
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
    public function blocks_have_visible_content_by_default(): void
    {
        $this->assertTrue((new LiveEditableBlock)->hasVisibleContent());
        $this->assertFalse((new HiddenContentBlock)->hasVisibleContent());
    }

    /** @test */
    public function only_the_empty_block_root_is_marked_hidden(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        $html = $this->renderHome();

        $this->assertSame(1, substr_count($html, 'data-pb-hidden=""'));
        $this->assertStringContainsString('data-pb-hidden', $this->rootTag($html, 'hidden-content-block'));
        $this->assertStringNotContainsString('data-pb-hidden', $this->rootTag($html, 'live-editable-block'));
    }

    /**
     * The opening tag of a block's root: the one whose Livewire snapshot names the block.
     */
    protected function rootTag(string $html, string $name): string
    {
        preg_match_all('/<div [^>]*wire:snapshot="[^"]*"[^>]*>/', $html, $matches);

        foreach ($matches[0] as $tag) {
            if (str_contains($tag, $name)) {
                return $tag;
            }
        }

        $this->fail("No root tag found for {$name}");
    }

    /**
     * The first render of a fresh process, which is every request under php-fpm.
     *
     * Livewire attaches component hooks once, when it boots, and keeps the list in a static.
     * A hook registered after that is skipped by the app that registered it, then picked up
     * by every app booted later in the same process - so in a shared test process it looked
     * fine while on a real request it never ran. A process of its own is the real request.
     */
    #[Test]
    #[RunInSeparateProcess]
    public function the_first_render_of_a_fresh_process_is_marked(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        Livewire::test(HiddenContentBlock::class)
            ->assertSeeHtml('data-pb-hidden');
    }

    /** @test */
    public function every_gear_carries_the_marker_the_css_hides_it_by(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        $html = $this->renderHome();

        // Both blocks are live editable, so both keep their gear in the markup. Hiding is
        // the CSS rule's job, so the gear is still there when the block gains content.
        $this->assertSame(2, substr_count($html, ' data-pb-edit-gear'));
        $this->assertStringContainsString('[data-pb-edit-gear="wrapper"]:has(+ [data-pb-hidden], + * > [data-pb-hidden])', $html);
        $this->assertStringContainsString('[data-pb-hidden] [data-pb-edit-gear]', $html);
    }

    /** @test */
    public function nothing_is_marked_and_no_rule_is_emitted_while_live_edit_is_off(): void
    {
        $html = $this->renderHome();

        $this->assertStringNotContainsString('data-pb-hidden', $html);
    }

    /** @test */
    public function a_block_with_content_is_not_marked(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        Livewire::test(HiddenContentBlock::class, ['shown' => true])
            ->assertDontSeeHtml('data-pb-hidden');
    }

    /** @test */
    public function the_mark_follows_the_block_through_livewire_updates(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        Livewire::test(HiddenContentBlock::class)
            ->assertSeeHtml('data-pb-hidden')
            ->set('shown', true)
            ->assertDontSeeHtml('data-pb-hidden')
            ->set('shown', false)
            ->assertSeeHtml('data-pb-hidden');
    }

    /** @test */
    public function the_builder_canvas_is_never_marked(): void
    {
        app(PageBuilderUIService::class)->enableLiveEdit(true);

        Livewire::test(HiddenContentBlock::class, ['editMode' => true])
            ->assertDontSeeHtml('data-pb-hidden');
    }
}
