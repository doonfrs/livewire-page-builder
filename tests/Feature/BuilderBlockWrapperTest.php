<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\BuilderBlock;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class BuilderBlockWrapperTest extends TestCase
{
    /** @test */
    public function builder_block_wrapper_gets_width_classes_when_wrapping_row_block(): void
    {
        // Test that when BuilderBlock wraps a RowBlock, it gets the width classes
        $properties = [
            'mobileWidth' => 'w-full',
            'tabletWidth' => 'w-1/2',
            'desktopWidth' => 'w-2xs',
            'backgroundColor' => '#ff0000',
        ];

        $component = Livewire::test(BuilderBlock::class, [
            'blockAlias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
            'blockId' => 'test-block-id',
            'rowId' => 'test-row-id',
            'properties' => $properties,
            'blocks' => [],
            'editMode' => true,
        ]);

        $cssClasses = $component->get('cssClasses');

        // The BuilderBlock wrapper should contain the width classes
        $this->assertStringContainsString('w-full', $cssClasses,
            'BuilderBlock wrapper should contain mobile width class');
        $this->assertStringContainsString('@3xl:w-1/2', $cssClasses,
            'BuilderBlock wrapper should contain tablet width class');
        $this->assertStringContainsString('@5xl:w-2xs', $cssClasses,
            'BuilderBlock wrapper should contain desktop width class');
    }

    /** @test */
    public function builder_block_wrapper_gets_width_classes_for_regular_blocks_too(): void
    {
        // Test that BuilderBlock also applies width classes for regular blocks
        // Use a real block class that exists
        $properties = [
            'mobileWidth' => 'w-auto',
            'desktopWidth' => 'w-xl',
        ];

        $component = Livewire::test(BuilderBlock::class, [
            'blockAlias' => 'page-builder-trinavo-livewire-page-builder-blocks-spacer',
            'blockId' => 'test-block-id',
            'rowId' => 'test-row-id',
            'properties' => $properties,
            'blocks' => [],
            'editMode' => true,
        ]);

        $cssClasses = $component->get('cssClasses');

        // The BuilderBlock wrapper should contain the width classes for regular blocks too
        $this->assertNotNull($cssClasses, 'CSS classes should not be null');
        $this->assertStringContainsString('w-auto', $cssClasses,
            'BuilderBlock wrapper should contain mobile width class for regular blocks');
        $this->assertStringContainsString('@5xl:w-xl', $cssClasses,
            'BuilderBlock wrapper should contain desktop width class for regular blocks');
    }

    /** @test */
    public function properties_are_updated_on_builder_block_wrapper(): void
    {
        // Test that property updates trigger CSS class updates on the wrapper
        $component = Livewire::test(BuilderBlock::class, [
            'blockAlias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
            'blockId' => 'test-block-id',
            'rowId' => 'test-row-id',
            'properties' => ['desktopWidth' => 'w-full'],
            'blocks' => [],
            'editMode' => true,
        ]);

        // Initial state
        $this->assertStringContainsString('w-full', $component->get('cssClasses'));

        // Update property
        $component->call('updateBlockProperty', null, 'test-block-id', 'desktopWidth', 'w-1/2');

        // Check that CSS classes were updated
        $this->assertStringContainsString('w-1/2', $component->get('cssClasses'));
        $this->assertStringNotContainsString('w-full', $component->get('cssClasses'));
    }
}
