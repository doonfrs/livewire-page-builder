<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Blocks\RichText;
use Trinavo\LivewirePageBuilder\Blocks\SimpleText;
use Trinavo\LivewirePageBuilder\Http\Livewire\LiveEdit;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Services\PageBuilderRender;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Services\PageBuilderUIService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

/**
 * The live edit sheet over the package's own text blocks: which fields it draws, how tall
 * it opens, and what a save writes.
 */
class LiveEditCoreBlocksTest extends TestCase
{
    protected Theme $theme;

    protected string $richAlias;

    protected string $simpleAlias;

    protected function setUp(): void
    {
        parent::setUp();

        $service = app(PageBuilderService::class);
        $this->richAlias = $service->getClassAlias(RichText::class);
        $this->simpleAlias = $service->getClassAlias(SimpleText::class);
        $this->theme = Theme::create(['name' => 'Test Theme', 'description' => 'Test']);

        BuilderPage::create([
            'key' => 'home',
            'theme_id' => $this->theme->id,
            'components' => [
                'row-1' => [
                    'properties' => [],
                    'blocks' => [
                        'block-rich' => [
                            'alias' => $this->richAlias,
                            'properties' => ['content' => '<p>Before</p>'],
                        ],
                        'block-simple' => [
                            'alias' => $this->simpleAlias,
                            'properties' => ['content' => 'Before'],
                        ],
                    ],
                ],
            ],
        ]);

        app(PageBuilderUIService::class)->enableLiveEdit(true);
    }

    protected function context(array $path): array
    {
        return ['page' => 'home', 'theme' => $this->theme->id, 'path' => $path];
    }

    protected function components(): array
    {
        return BuilderPage::where('key', 'home')->where('theme_id', $this->theme->id)->first()->components;
    }

    /** @test */
    public function opening_rich_text_puts_the_editor_first_then_the_style_groups(): void
    {
        $component = Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context(['row-1', 'block-rich']));

        // 'general' is the ungrouped bucket OrganizesBlockProperties forces to the front,
        // and 'content' is the only thing in it.
        $this->assertSame(['general', 'text', 'color', 'font'], array_keys($component->get('propertyGroups')));

        $this->assertSame(
            ['content'],
            array_column($component->get('propertyGroups')['general']['properties'], 'name')
        );
    }

    /** @test */
    public function the_sheet_opens_tall_for_rich_text_and_short_for_simple_text(): void
    {
        // The rendered class name is the real guard here: the two caps have to survive in
        // the markup as whole literals or the host's Tailwind never compiles them.
        Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context(['row-1', 'block-rich']))
            ->assertSet('needsRoom', true)
            ->assertSee('max-h-[75dvh]', escape: false);

        Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context(['row-1', 'block-simple']))
            ->assertSet('needsRoom', false)
            ->assertSee('max-h-[40dvh]', escape: false);
    }

    /** @test */
    public function reopening_on_a_short_block_shrinks_the_sheet_again(): void
    {
        Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context(['row-1', 'block-rich']))
            ->assertSet('needsRoom', true)
            ->call('openBlock', $this->context(['row-1', 'block-simple']))
            ->assertSet('needsRoom', false);
    }

    /** @test */
    public function multilingual_content_survives_a_save(): void
    {
        $value = [
            'multilingual' => true,
            'values' => ['en' => '<p>After</p>'],
            'default_locale' => 'en',
        ];

        Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context(['row-1', 'block-rich']))
            ->call('updateBlockProperty', null, null, 'content', $value)
            ->call('save');

        $stored = $this->components()['row-1']['blocks']['block-rich']['properties']['content'];

        $this->assertTrue($stored['multilingual']);
        $this->assertSame('<p>After</p>', $stored['values']['en']);
        $this->assertSame('en', $stored['default_locale']);
    }

    /** @test */
    public function saving_the_text_does_not_reload_but_saving_the_alignment_does(): void
    {
        // The block renders its own content, so preview already painted it.
        $content = Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context(['row-1', 'block-simple']))
            ->call('updateBlockProperty', null, null, 'content', 'After')
            ->call('save');

        $this->assertStringNotContainsString('location.reload', json_encode($content->effects['xjs'] ?? []));
        $this->assertSame('After', $this->components()['row-1']['blocks']['block-simple']['properties']['content']);

        // Alignment is drawn on the wrapper by the parent row, out of preview's reach.
        $align = Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context(['row-1', 'block-simple']))
            ->call('updateBlockProperty', null, null, 'textAlign', 'text-center')
            ->call('save');

        $js = json_encode($align->effects['xjs'] ?? []);

        $this->assertStringContainsString('window.location.reload()', $js);
        $this->assertStringContainsString('pbLiveEditScroll', $js);
    }

    /** @test */
    public function a_property_the_block_did_not_declare_is_rejected(): void
    {
        Livewire::test(LiveEdit::class)
            ->call('openBlock', $this->context(['row-1', 'block-rich']))
            ->call('updateBlockProperty', null, null, 'backgroundColor', 'red')
            ->call('save');

        $this->assertArrayNotHasKey(
            'backgroundColor',
            $this->components()['row-1']['blocks']['block-rich']['properties']
        );
    }

    /** @test */
    public function both_core_text_blocks_draw_a_gear_on_the_public_page(): void
    {
        $rows = app(PageBuilderRender::class)->parsePage('home', $this->theme->id)['rows'];

        $html = Blade::render(
            '@foreach ($rows as $row)<x-page-builder::row-view :row="$row" />@endforeach',
            ['rows' => $rows]
        );

        $this->assertSame(2, substr_count($html, "Livewire.dispatch('pb-live-edit'"));
    }
}
