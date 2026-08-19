<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Blocks\Spacer;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Support\Properties\BlockProperty;
use Trinavo\LivewirePageBuilder\Tests\Fixtures\LiveEditableBlock;
use Trinavo\LivewirePageBuilder\Tests\Fixtures\PlainBlock;
use Trinavo\LivewirePageBuilder\Tests\Fixtures\SpacingLiveEditBlock;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class LiveEditPropertiesTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        config()->set('page-builder.blocks', [
            LiveEditableBlock::class,
            PlainBlock::class,
            SpacingLiveEditBlock::class,
        ]);
    }

    /** @test */
    public function blocks_are_not_live_editable_by_default(): void
    {
        $this->assertSame([], (new Spacer)->getPageBuilderLiveEditProperties());
        $this->assertFalse((new Spacer)->hasLiveEditProperties());
        $this->assertSame([], (new Spacer)->getLiveEditPropertyKeys());

        $this->assertFalse((new PlainBlock)->hasLiveEditProperties());
    }

    /** @test */
    public function declaring_properties_makes_a_block_live_editable(): void
    {
        $block = new LiveEditableBlock;

        $this->assertTrue($block->hasLiveEditProperties());
        $this->assertContainsOnlyInstancesOf(BlockProperty::class, $block->resolveLiveEditProperties());
    }

    /** @test */
    public function string_entries_resolve_against_all_properties_and_unknown_names_are_dropped(): void
    {
        $keys = (new LiveEditableBlock)->getLiveEditPropertyKeys();

        // 'title' came through as an object, 'subtitle' as a name, 'noSuchProperty' is gone.
        $this->assertSame(['title', 'subtitle'], $keys);
        $this->assertNotContains('noSuchProperty', $keys);
    }

    /** @test */
    public function properties_outside_the_declaration_are_not_writable(): void
    {
        $keys = (new LiveEditableBlock)->getLiveEditPropertyKeys();

        $this->assertNotContains('internalNote', $keys, 'a declared-but-not-live property leaked');
        $this->assertNotContains('desktopWidth', $keys, 'a shared style property leaked');
        $this->assertNotContains('editMode', $keys);
    }

    /** @test */
    public function responsive_spacing_expands_into_its_generated_field_names(): void
    {
        $keys = (new SpacingLiveEditBlock)->getLiveEditPropertyKeys();

        $this->assertCount(12, $keys);
        foreach (['desktop', 'tablet', 'mobile'] as $device) {
            foreach (['Top', 'Right', 'Bottom', 'Left'] as $direction) {
                $this->assertContains($device.'Padding'.$direction, $keys);
            }
        }
    }

    /** @test */
    public function the_service_reports_live_editability_per_alias(): void
    {
        $service = app(PageBuilderService::class);

        $this->assertTrue($service->blockHasLiveEditProperties($service->getClassAlias(LiveEditableBlock::class)));
        $this->assertFalse($service->blockHasLiveEditProperties($service->getClassAlias(PlainBlock::class)));

        // Rows, embedded pages, unknown aliases and null must all answer false rather than throw.
        $this->assertFalse($service->blockHasLiveEditProperties('row-block'));
        $this->assertFalse($service->blockHasLiveEditProperties('builder-page-block'));
        $this->assertFalse($service->blockHasLiveEditProperties('page-builder-does-not-exist'));
        $this->assertFalse($service->blockHasLiveEditProperties(null));
    }

    /** @test */
    public function the_alias_memo_does_not_leak_between_aliases(): void
    {
        $service = app(PageBuilderService::class);

        // Prime the cache with a negative answer first, then ask about a live editable one.
        $this->assertFalse($service->blockHasLiveEditProperties($service->getClassAlias(PlainBlock::class)));
        $this->assertTrue($service->blockHasLiveEditProperties($service->getClassAlias(LiveEditableBlock::class)));
        $this->assertFalse($service->blockHasLiveEditProperties($service->getClassAlias(PlainBlock::class)));
    }

    /** @test */
    public function the_live_edit_schema_is_the_array_form_the_widgets_expect(): void
    {
        $service = app(PageBuilderService::class);
        $schema = $service->getLiveEditSchema($service->getClassAlias(LiveEditableBlock::class));

        $this->assertCount(2, $schema);
        $this->assertSame('title', $schema[0]['name']);
        $this->assertSame('text', $schema[0]['type']);
        $this->assertArrayHasKey('label', $schema[1]);
    }
}
