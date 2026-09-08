<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Blocks\RichText;
use Trinavo\LivewirePageBuilder\Blocks\SimpleText;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Support\Properties\ColorProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\RichTextProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\SelectProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\SimpleTextProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

/**
 * The two blocks a store owner actually writes words into are live editable out of the
 * box, so enabling live edit puts a gear on them without the host declaring anything.
 *
 * The core blocks are always registered by PageBuilderService::getConfigBlocks(), so
 * none of this needs a page-builder.blocks config.
 */
class CoreBlockLiveEditTest extends TestCase
{
    /**
     * The declared order is the sheet's field order, so it is the assertion.
     */
    protected array $expectedKeys = [
        'content',
        'textAlign',
        'textColor',
        'mobileFontSize',
        'tabletFontSize',
        'desktopFontSize',
    ];

    /** @test */
    public function the_core_text_blocks_are_live_editable_out_of_the_box(): void
    {
        $this->assertTrue((new RichText)->hasLiveEditProperties());
        $this->assertTrue((new SimpleText)->hasLiveEditProperties());
    }

    /** @test */
    public function rich_text_exposes_its_content_first_then_the_basic_type_settings(): void
    {
        $this->assertSame($this->expectedKeys, (new RichText)->getLiveEditPropertyKeys());
    }

    /** @test */
    public function simple_text_exposes_the_same_set(): void
    {
        $this->assertSame($this->expectedKeys, (new SimpleText)->getLiveEditPropertyKeys());
    }

    /** @test */
    public function nothing_outside_the_declaration_comes_along(): void
    {
        foreach ([new RichText, new SimpleText] as $block) {
            $keys = $block->getLiveEditPropertyKeys();

            $this->assertNotContains('backgroundColor', $keys);
            $this->assertNotContains('desktopWidth', $keys);
            $this->assertNotContains('editMode', $keys);
        }
    }

    /** @test */
    public function the_live_edit_schema_uses_the_editor_widgets(): void
    {
        $service = app(PageBuilderService::class);

        $types = fn (string $class) => collect($service->getLiveEditSchema($service->getClassAlias($class)))
            ->pluck('type', 'name')
            ->all();

        $richText = $types(RichText::class);
        $this->assertSame('richtext', $richText['content']);
        $this->assertSame('select', $richText['textAlign']);
        $this->assertSame('color', $richText['textColor']);
        $this->assertSame('select', $richText['mobileFontSize']);

        $this->assertSame('simpletext', $types(SimpleText::class)['content']);
    }

    /**
     * Only the editor asks for the tall sheet. Everything else has to fit in the short
     * one, or the page being edited stops being visible behind it.
     *
     * @test
     */
    public function only_the_rich_text_field_asks_for_a_taller_sheet(): void
    {
        $this->assertTrue((new RichTextProperty('content'))->needsRoom());

        $this->assertFalse((new SimpleTextProperty('content'))->needsRoom());
        $this->assertFalse((new TextProperty('note'))->needsRoom());
        $this->assertFalse((new ColorProperty('textColor'))->needsRoom());
        $this->assertFalse((new SelectProperty('textAlign', options: []))->needsRoom());
    }
}
