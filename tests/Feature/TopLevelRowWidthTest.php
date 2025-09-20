<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class TopLevelRowWidthTest extends TestCase
{
    /** @test */
    public function top_level_row_includes_width_classes(): void
    {
        // Test that top-level rows (not nested) include width classes
        $properties = [
            'mobileWidth' => 'w-full',
            'tabletWidth' => 'w-1/2',
            'desktopWidth' => 'w-2xs',
            'backgroundColor' => '#ff0000',
        ];

        $component = Livewire::test(RowBlock::class, [
            'rowId' => 'test-row-id',
            'properties' => $properties,
            'blocks' => [],
            'editMode' => true,
            'isNested' => false, // This is a top-level row
        ]);

        $cssClasses = $component->get('cssClasses');

        // Top-level rows should contain width classes
        $this->assertStringContainsString('w-full', $cssClasses,
            'Top-level row should contain mobile width class');
        $this->assertStringContainsString('@3xl:w-1/2', $cssClasses,
            'Top-level row should contain tablet width class');
        $this->assertStringContainsString('@5xl:w-2xs', $cssClasses,
            'Top-level row should contain desktop width class');
    }

    /** @test */
    public function nested_row_excludes_width_classes(): void
    {
        // Test that nested rows don't include width classes (they go to BuilderBlock wrapper)
        $properties = [
            'mobileWidth' => 'w-full',
            'tabletWidth' => 'w-1/2',
            'desktopWidth' => 'w-2xs',
            'backgroundColor' => '#ff0000',
        ];

        $component = Livewire::test(RowBlock::class, [
            'rowId' => 'test-row-id',
            'properties' => $properties,
            'blocks' => [],
            'editMode' => true,
            'isNested' => true, // This is a nested row
        ]);

        $cssClasses = $component->get('cssClasses');

        // Nested rows should NOT contain width classes
        $this->assertStringNotContainsString('w-full', $cssClasses,
            'Nested row should NOT contain mobile width class');
        $this->assertStringNotContainsString('@3xl:w-1/2', $cssClasses,
            'Nested row should NOT contain tablet width class');
        $this->assertStringNotContainsString('@5xl:w-2xs', $cssClasses,
            'Nested row should NOT contain desktop width class');

        // But should still contain other properties like centering
        $this->assertStringContainsString('items-center', $cssClasses,
            'Nested row should still contain other properties like centering');
    }

    /** @test */
    public function property_update_preserves_width_for_top_level_row(): void
    {
        // Test that property updates on top-level rows preserve width classes
        $component = Livewire::test(RowBlock::class, [
            'rowId' => 'test-row-id',
            'properties' => ['desktopWidth' => 'w-full'],
            'blocks' => [],
            'editMode' => true,
            'isNested' => false, // Top-level row
        ]);

        // Initial state should have width
        $this->assertStringContainsString('w-full', $component->get('cssClasses'));

        // Update desktop width property (this will trigger CSS regeneration)
        $component->call('updateBlockProperty', 'test-row-id', null, 'desktopWidth', 'w-2xs');

        // Should now have the updated desktop width in responsive format
        $this->assertStringContainsString('@5xl:w-2xs', $component->get('cssClasses'));
        // Mobile width should still be w-full (as that's the default mobile width)
        $this->assertStringContainsString('w-full', $component->get('cssClasses'));
    }
}