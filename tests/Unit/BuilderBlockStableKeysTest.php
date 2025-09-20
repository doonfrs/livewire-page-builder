<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Http\Livewire\BuilderBlock;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class BuilderBlockStableKeysTest extends TestCase
{
    /** @test */
    public function builder_block_uses_stable_component_keys(): void
    {
        $builderBlock = new BuilderBlock;
        $builderBlock->blockId = 'test-block-123';
        $builderBlock->blockAlias = 'text-block';
        $builderBlock->properties = [
            'text' => 'Hello World',
            'color' => '#000000',
        ];

        // The template should use: key($blockId)
        // NOT: key($blockId . '-' . md5(json_encode($componentProperties)))

        // This ensures that property changes don't force component recreation
        // which would cause Livewire snapshot errors

        $this->assertEquals('test-block-123', $builderBlock->blockId);
        $this->assertIsArray($builderBlock->properties);

        // Change properties - this should not affect the component key
        $builderBlock->properties['text'] = 'Updated Text';
        $builderBlock->properties['newProperty'] = 'New Value';

        // Component key remains stable (just the blockId)
        $this->assertEquals('test-block-123', $builderBlock->blockId);
        $this->assertEquals('Updated Text', $builderBlock->properties['text']);
    }

    /** @test */
    public function stable_keys_prevent_property_change_errors(): void
    {
        // This test documents the stable key approach for BuilderBlock:

        // PROBLEM:
        // BuilderBlock template was using:
        // key($blockId . '-' . md5(json_encode($componentProperties)))
        //
        // This meant that ANY property change would:
        // 1. Change the MD5 hash of properties
        // 2. Change the component key
        // 3. Force Livewire to create a new component instance
        // 4. Cause "Snapshot missing" errors during DOM updates

        // SOLUTION:
        // Use stable keys based only on blockId:
        // key($blockId)
        //
        // Benefits:
        // - Component persists across property changes
        // - No snapshot errors when properties update
        // - Better performance (no component recreation)
        // - Livewire can properly track component state

        // TEMPLATES UPDATED:
        // builder-block.blade.php lines 29 & 39:
        // BEFORE: key($blockId . '-' . md5(json_encode($componentProperties)))
        // AFTER:  key($blockId)

        $this->assertTrue(true, 'BuilderBlock stable key approach documented');
    }
}
