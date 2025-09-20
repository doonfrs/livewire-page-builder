<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\BuilderBlock;
use Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class VerticalAlignmentPropertyTest extends TestCase
{
    /** @test */
    public function row_block_has_vertical_alignment_property_with_default_value(): void
    {
        $rowBlock = new RowBlock;

        $this->assertEquals('content-center', $rowBlock->contentAlign,
            'RowBlock should have default contentAlign of items-center');
    }

    /** @test */
    public function row_block_includes_vertical_alignment_in_page_builder_properties(): void
    {
        $rowBlock = new RowBlock;
        $properties = $rowBlock->getPageBuilderProperties();

        // Find the contentAlign property
        $verticalAlignProperty = null;
        foreach ($properties as $property) {
            if ($property->name === 'contentAlign') {
                $verticalAlignProperty = $property;
                break;
            }
        }

        $this->assertNotNull($verticalAlignProperty, 'contentAlign property should exist in page builder properties');
        $this->assertEquals('Content Alignment', $verticalAlignProperty->label);
        $this->assertEquals('content-center', $verticalAlignProperty->defaultValue);

        // Check available options
        $expectedOptions = [
            'content-start' => 'Top',
            'content-center' => 'Center',
            'content-end' => 'Bottom',
            'content-stretch' => 'Stretch',
        ];
        $this->assertEquals($expectedOptions, $verticalAlignProperty->options);
    }

    /** @test */
    public function page_builder_service_uses_vertical_align_property_in_css_generation(): void
    {
        $service = app(PageBuilderService::class);

        // Test default behavior (items-center)
        $defaultProperties = ['flex' => 'row'];
        $defaultCss = $service->getCssClassesFromProperties($defaultProperties);
        $this->assertStringContainsString('content-center', $defaultCss,
            'Default CSS should contain content-center when no contentAlign is specified');

        // Test items-start (top alignment)
        $topProperties = ['flex' => 'row', 'contentAlign' => 'content-start'];
        $topCss = $service->getCssClassesFromProperties($topProperties);
        $this->assertStringContainsString('content-start', $topCss,
            'CSS should contain content-start when contentAlign is set to content-start');
        $this->assertStringNotContainsString('content-center', $topCss,
            'CSS should not contain content-center when contentAlign is set to content-start');

        // Test items-end (bottom alignment)
        $bottomProperties = ['flex' => 'row', 'contentAlign' => 'content-end'];
        $bottomCss = $service->getCssClassesFromProperties($bottomProperties);
        $this->assertStringContainsString('content-end', $bottomCss,
            'CSS should contain content-end when contentAlign is set to content-end');
        $this->assertStringNotContainsString('content-center', $bottomCss,
            'CSS should not contain content-center when contentAlign is set to content-end');

        // Test items-stretch (stretch alignment)
        $stretchProperties = ['flex' => 'row', 'contentAlign' => 'content-stretch'];
        $stretchCss = $service->getCssClassesFromProperties($stretchProperties);
        $this->assertStringContainsString('content-stretch', $stretchCss,
            'CSS should contain content-stretch when contentAlign is set to content-stretch');
        $this->assertStringNotContainsString('content-center', $stretchCss,
            'CSS should not contain content-center when contentAlign is set to content-stretch');
    }

    /** @test */
    public function row_block_livewire_component_applies_vertical_alignment_to_css(): void
    {
        // Test with default alignment
        $defaultComponent = Livewire::test(RowBlock::class, [
            'rowId' => 'test-row-default',
            'properties' => ['flex' => 'row'],
            'blocks' => [],
            'editMode' => true,
        ]);

        $defaultCssClasses = $defaultComponent->get('cssClasses');
        $this->assertStringContainsString('content-center', $defaultCssClasses,
            'RowBlock CSS should contain default items-center alignment');

        // Test with top alignment
        $topComponent = Livewire::test(RowBlock::class, [
            'rowId' => 'test-row-top',
            'properties' => ['flex' => 'row', 'contentAlign' => 'content-start'],
            'blocks' => [],
            'editMode' => true,
        ]);

        $topCssClasses = $topComponent->get('cssClasses');
        $this->assertStringContainsString('content-start', $topCssClasses,
            'RowBlock CSS should contain items-start when contentAlign is set to items-start');
        $this->assertStringNotContainsString('content-center', $topCssClasses,
            'RowBlock CSS should not contain items-center when using custom alignment');

        // Test with bottom alignment
        $bottomComponent = Livewire::test(RowBlock::class, [
            'rowId' => 'test-row-bottom',
            'properties' => ['flex' => 'row', 'contentAlign' => 'items-end'],
            'blocks' => [],
            'editMode' => true,
        ]);

        $bottomCssClasses = $bottomComponent->get('cssClasses');
        $this->assertStringContainsString('items-end', $bottomCssClasses,
            'RowBlock CSS should contain items-end when contentAlign is set to items-end');
    }

    /** @test */
    public function builder_block_wrapper_applies_vertical_alignment_for_nested_rows(): void
    {
        // Test BuilderBlock wrapper with vertical alignment for nested RowBlock
        $component = Livewire::test(BuilderBlock::class, [
            'blockAlias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
            'blockId' => 'nested-row-123',
            'rowId' => 'parent-row-456',
            'properties' => [
                'flex' => 'row',
                'contentAlign' => 'items-start',
                'desktopHeight' => 'h-screen',
            ],
            'blocks' => [],
            'editMode' => true,
        ]);

        $cssClasses = $component->get('cssClasses');
        $this->assertStringContainsString('items-start', $cssClasses,
            'BuilderBlock wrapper should apply vertical alignment for nested rows');
        $this->assertStringContainsString('h-screen', $cssClasses,
            'BuilderBlock should include height class for tall containers');
    }

    /** @test */
    public function vertical_alignment_property_updates_work_with_livewire(): void
    {
        // Test updating contentAlign property via Livewire
        $component = Livewire::test(RowBlock::class, [
            'rowId' => 'test-row-update',
            'properties' => ['flex' => 'row', 'contentAlign' => 'items-center'],
            'blocks' => [],
            'editMode' => true,
        ]);

        // Initial state
        $initialCss = $component->get('cssClasses');
        $this->assertStringContainsString('items-center', $initialCss);

        // Update to top alignment
        $component->call('updateBlockProperty', 'test-row-update', null, 'contentAlign', 'items-start');

        // Verify the update was applied
        $updatedProperties = $component->get('properties');
        $this->assertEquals('items-start', $updatedProperties['contentAlign'],
            'contentAlign property should be updated to items-start');

        $updatedCss = $component->get('cssClasses');
        $this->assertStringContainsString('items-start', $updatedCss,
            'Updated CSS should contain items-start');
        $this->assertStringNotContainsString('items-center', $updatedCss,
            'Updated CSS should not contain old items-center');
    }

    /** @test */
    public function all_vertical_alignment_options_generate_correct_css(): void
    {
        $service = app(PageBuilderService::class);

        $alignmentOptions = [
            'items-start' => 'Top alignment should generate items-start',
            'items-center' => 'Center alignment should generate items-center',
            'items-end' => 'Bottom alignment should generate items-end',
            'items-stretch' => 'Stretch alignment should generate items-stretch',
        ];

        foreach ($alignmentOptions as $alignmentClass => $description) {
            $properties = ['flex' => 'row', 'contentAlign' => $alignmentClass];
            $css = $service->getCssClassesFromProperties($properties);

            $this->assertStringContainsString($alignmentClass, $css, $description);

            // Ensure only the specified alignment class is present
            foreach (array_keys($alignmentOptions) as $otherAlignment) {
                if ($otherAlignment !== $alignmentClass) {
                    $this->assertStringNotContainsString($otherAlignment, $css,
                        "CSS should not contain $otherAlignment when using $alignmentClass");
                }
            }
        }
    }
}
