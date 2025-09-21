<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\BuilderBlock;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class BuilderBlockNestedRowPropertyUpdateTest extends TestCase
{
    /** @test */
    public function builder_block_updates_nested_row_size_properties_and_refreshes(): void
    {
        // Test the specific fix for nested row size property updates not reflecting in browser
        // This reproduces the issue where backgroundColor worked but desktopWidth didn't

        $initialProperties = [
            'mobileWidth' => 'w-full',
            'tabletWidth' => 'w-full',
            'desktopWidth' => 'w-full',
            'backgroundColor' => 'primary',
            'textColor' => null,
        ];

        $component = Livewire::test(BuilderBlock::class, [
            'blockAlias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
            'blockId' => 'nested-row-123',
            'rowId' => 'parent-row-456',
            'properties' => $initialProperties,
            'blocks' => [],
            'editMode' => true,
        ]);

        // Verify initial state
        $initialCssClasses = $component->get('cssClasses');
        $this->assertStringContainsString('w-full', $initialCssClasses,
            'Initial CSS should contain w-full width class');
        $this->assertStringContainsString('bg-primary', $initialCssClasses,
            'Initial CSS should contain bg-primary background class');

        // Test 1: Update a size property (desktopWidth) - this was the broken case
        $component->call('updateBlockProperty', 'nested-row-123', null, 'desktopWidth', 'w-2xs');

        // Verify the property was updated in the component
        $updatedProperties = $component->get('properties');
        $this->assertEquals('w-2xs', $updatedProperties['desktopWidth'],
            'desktopWidth property should be updated to w-2xs');

        // Verify CSS classes were regenerated with new width
        $updatedCssClasses = $component->get('cssClasses');
        $this->assertStringContainsString('w-2xs', $updatedCssClasses,
            'Updated CSS should contain w-2xs width class');
        $this->assertStringNotContainsString('@5xl:w-full', $updatedCssClasses,
            'Updated CSS should not contain old w-full desktop width');

        // Test 2: Update a non-size property (backgroundColor) - this already worked
        $component->call('updateBlockProperty', 'nested-row-123', null, 'backgroundColor', 'accent');

        // Verify the property was updated
        $secondUpdatedProperties = $component->get('properties');
        $this->assertEquals('accent', $secondUpdatedProperties['backgroundColor'],
            'backgroundColor property should be updated to accent');

        // Verify CSS classes include new background
        $secondUpdatedCssClasses = $component->get('cssClasses');
        $this->assertStringContainsString('bg-accent', $secondUpdatedCssClasses,
            'Updated CSS should contain bg-accent background class');
        $this->assertStringNotContainsString('bg-primary', $secondUpdatedCssClasses,
            'Updated CSS should not contain old bg-primary background');

        // Test 3: Verify both size and non-size properties are applied together
        $this->assertStringContainsString('w-2xs', $secondUpdatedCssClasses,
            'Final CSS should still contain w-2xs width');
        $this->assertStringContainsString('bg-accent', $secondUpdatedCssClasses,
            'Final CSS should still contain bg-accent background');

        // Test 4: Update mobile width to ensure all size properties work
        $component->call('updateBlockProperty', 'nested-row-123', null, 'mobileWidth', 'w-auto');

        $finalProperties = $component->get('properties');
        $finalCssClasses = $component->get('cssClasses');

        $this->assertEquals('w-auto', $finalProperties['mobileWidth'],
            'mobileWidth property should be updated to w-auto');
        $this->assertStringContainsString('w-auto', $finalCssClasses,
            'Final CSS should contain w-auto mobile width');
    }

    /** @test */
    public function builder_block_ignores_updates_for_wrong_row_id(): void
    {
        // Test that BuilderBlock only responds to updates targeting its specific nested row

        $component = Livewire::test(BuilderBlock::class, [
            'blockAlias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
            'blockId' => 'nested-row-123',
            'rowId' => 'parent-row-456',
            'properties' => ['desktopWidth' => 'w-full'],
            'blocks' => [],
            'editMode' => true,
        ]);

        $initialProperties = $component->get('properties');
        $initialCssClasses = $component->get('cssClasses');

        // Try to update with wrong row ID - should be ignored
        $component->call('updateBlockProperty', 'different-row-789', null, 'desktopWidth', 'w-2xs');

        // Verify nothing changed
        $unchangedProperties = $component->get('properties');
        $unchangedCssClasses = $component->get('cssClasses');

        $this->assertEquals($initialProperties, $unchangedProperties,
            'Properties should remain unchanged when wrong rowId is used');
        $this->assertEquals($initialCssClasses, $unchangedCssClasses,
            'CSS classes should remain unchanged when wrong rowId is used');
    }

    /** @test */
    public function builder_block_handles_regular_block_updates_correctly(): void
    {
        // Test that regular block updates (non-nested rows) still work correctly

        $component = Livewire::test(BuilderBlock::class, [
            'blockAlias' => 'page-builder-trinavo-livewire-page-builder-blocks-spacer',
            'blockId' => 'regular-block-123',
            'rowId' => 'parent-row-456',
            'properties' => ['desktopWidth' => 'w-full', 'backgroundColor' => 'primary'],
            'blocks' => [],
            'editMode' => true,
        ]);

        // Update regular block property (no rowId, blockId matches)
        $component->call('updateBlockProperty', null, 'regular-block-123', 'desktopWidth', 'w-1/2');

        $updatedProperties = $component->get('properties');
        $updatedCssClasses = $component->get('cssClasses');

        $this->assertEquals('w-1/2', $updatedProperties['desktopWidth'],
            'Regular block desktopWidth should be updated');
        $this->assertStringContainsString('w-1/2', $updatedCssClasses,
            'Regular block CSS should contain new width');
    }

    /** @test */
    public function builder_block_css_generation_for_nested_rows_includes_sizing(): void
    {
        // Test that BuilderBlock wrapper correctly applies sizing properties for nested RowBlocks
        // This is critical because nested RowBlocks exclude sizing from their own CSS

        $properties = [
            'mobileWidth' => 'w-auto',
            'tabletWidth' => 'w-1/2',
            'desktopWidth' => 'w-2xs',
            'backgroundColor' => 'secondary',
            'textColor' => 'white',
            'paddingTop' => 4,
            'marginLeft' => 2,
        ];

        $component = Livewire::test(BuilderBlock::class, [
            'blockAlias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
            'blockId' => 'nested-row-123',
            'rowId' => 'parent-row-456',
            'properties' => $properties,
            'blocks' => [],
            'editMode' => true,
        ]);

        $cssClasses = $component->get('cssClasses');

        // Verify all size properties are included in BuilderBlock wrapper CSS
        $this->assertStringContainsString('w-auto', $cssClasses,
            'BuilderBlock should include mobile width for nested RowBlock');
        $this->assertStringContainsString('@3xl:w-1/2', $cssClasses,
            'BuilderBlock should include tablet width for nested RowBlock');
        $this->assertStringContainsString('@5xl:w-2xs', $cssClasses,
            'BuilderBlock should include desktop width for nested RowBlock');

        // Verify other properties are also included
        $this->assertStringContainsString('bg-secondary', $cssClasses,
            'BuilderBlock should include background color');
        $this->assertStringContainsString('text-white', $cssClasses,
            'BuilderBlock should include text color');
        $this->assertStringContainsString('pt-4', $cssClasses,
            'BuilderBlock should include padding');
        $this->assertStringContainsString('ml-2', $cssClasses,
            'BuilderBlock should include margin');
    }

    /** @test */
    public function multiple_nested_row_property_updates_accumulate_correctly(): void
    {
        // Test that multiple rapid property updates work correctly

        $component = Livewire::test(BuilderBlock::class, [
            'blockAlias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
            'blockId' => 'nested-row-123',
            'rowId' => 'parent-row-456',
            'properties' => [
                'mobileWidth' => 'w-full',
                'tabletWidth' => 'w-full',
                'desktopWidth' => 'w-full',
                'backgroundColor' => null,
                'textColor' => null,
            ],
            'blocks' => [],
            'editMode' => true,
        ]);

        // Perform multiple updates in sequence
        $component->call('updateBlockProperty', 'nested-row-123', null, 'desktopWidth', 'w-1/4');
        $component->call('updateBlockProperty', 'nested-row-123', null, 'backgroundColor', 'primary');
        $component->call('updateBlockProperty', 'nested-row-123', null, 'tabletWidth', 'w-1/2');
        $component->call('updateBlockProperty', 'nested-row-123', null, 'textColor', 'white');
        $component->call('updateBlockProperty', 'nested-row-123', null, 'mobileWidth', 'w-auto');

        // Verify all updates are reflected in properties
        $finalProperties = $component->get('properties');
        $this->assertEquals('w-1/4', $finalProperties['desktopWidth']);
        $this->assertEquals('primary', $finalProperties['backgroundColor']);
        $this->assertEquals('w-1/2', $finalProperties['tabletWidth']);
        $this->assertEquals('white', $finalProperties['textColor']);
        $this->assertEquals('w-auto', $finalProperties['mobileWidth']);

        // Verify all updates are reflected in CSS
        $finalCssClasses = $component->get('cssClasses');
        $this->assertStringContainsString('@5xl:w-1/4', $finalCssClasses, 'Desktop width');
        $this->assertStringContainsString('bg-primary', $finalCssClasses, 'Background color');
        $this->assertStringContainsString('@3xl:w-1/2', $finalCssClasses, 'Tablet width');
        $this->assertStringContainsString('text-white', $finalCssClasses, 'Text color');
        $this->assertStringContainsString('w-auto', $finalCssClasses, 'Mobile width');
    }
}
