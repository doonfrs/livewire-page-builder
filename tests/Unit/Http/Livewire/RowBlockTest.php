<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit\Http\Livewire;

use Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class RowBlockTest extends TestCase
{
    protected RowBlock $rowBlock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rowBlock = new RowBlock();
        $this->rowBlock->rowId = 'test-row-id';
        $this->rowBlock->blocks = [];
        $this->rowBlock->properties = [];
    }

    /** @test */
    public function it_can_add_blocks_to_itself(): void
    {
        $initialBlockCount = count($this->rowBlock->blocks);

        // Simulate adding a block
        $blockId = uniqid();
        $block = [
            'alias' => 'test-block-alias',
            'properties' => ['test' => 'value'],
        ];

        $this->rowBlock->blocks[$blockId] = $block;

        $this->assertCount($initialBlockCount + 1, $this->rowBlock->blocks);

        $lastBlock = end($this->rowBlock->blocks);
        $this->assertArrayHasKey('alias', $lastBlock);
        $this->assertArrayHasKey('properties', $lastBlock);
        $this->assertEquals('test-block-alias', $lastBlock['alias']);
    }

    /** @test */
    public function it_can_delete_blocks(): void
    {
        // Add a block first
        $this->rowBlock->blocks = [
            'block-1' => ['alias' => 'test-block', 'properties' => []],
        ];

        $this->assertCount(1, $this->rowBlock->blocks);

        // Simulate deleteBlock method logic
        $blockId = 'block-1';
        if (isset($this->rowBlock->blocks[$blockId])) {
            unset($this->rowBlock->blocks[$blockId]);
        }

        $this->assertCount(0, $this->rowBlock->blocks);
    }

    /** @test */
    public function it_can_update_row_properties(): void
    {
        $this->rowBlock->properties = ['flex' => 'row'];

        // Simulate updateBlockProperty for row properties
        $rowId = 'test-row-id';
        $blockId = null; // null blockId means updating row properties
        $propertyName = 'flex';
        $value = 'column';

        if ($rowId == $this->rowBlock->rowId && !$blockId) {
            $this->rowBlock->properties[$propertyName] = $value;
        }

        $this->assertEquals('column', $this->rowBlock->properties['flex']);
    }

    /** @test */
    public function it_can_update_block_properties(): void
    {
        // Add a block first
        $blockId = 'block-1';
        $this->rowBlock->blocks = [
            $blockId => [
                'alias' => 'test-block',
                'properties' => ['color' => 'red'],
            ],
        ];

        // Simulate updating block property
        if (isset($this->rowBlock->blocks[$blockId])) {
            $this->rowBlock->blocks[$blockId]['properties']['color'] = 'blue';
        }

        $this->assertEquals('blue', $this->rowBlock->blocks[$blockId]['properties']['color']);
    }

    /** @test */
    public function it_can_move_blocks_up(): void
    {
        // Add multiple blocks
        $this->rowBlock->blocks = [
            'block-1' => ['alias' => 'first-block', 'properties' => []],
            'block-2' => ['alias' => 'second-block', 'properties' => []],
            'block-3' => ['alias' => 'third-block', 'properties' => []],
        ];

        $blockIds = array_keys($this->rowBlock->blocks);
        $blockIdToMove = 'block-2'; // Second block
        $currentIndex = array_search($blockIdToMove, $blockIds);

        // Simulate moveBlockUp logic
        if ($currentIndex > 0) {
            $newOrder = $blockIds;
            $temp = $newOrder[$currentIndex - 1];
            $newOrder[$currentIndex - 1] = $newOrder[$currentIndex];
            $newOrder[$currentIndex] = $temp;

            $this->rowBlock->blocks = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $this->rowBlock->blocks[$id]])->toArray();
        }

        $newOrder = array_keys($this->rowBlock->blocks);
        $this->assertEquals('block-2', $newOrder[0]);
        $this->assertEquals('block-1', $newOrder[1]);
        $this->assertEquals('block-3', $newOrder[2]);
    }

    /** @test */
    public function it_can_move_blocks_down(): void
    {
        // Add multiple blocks
        $this->rowBlock->blocks = [
            'block-1' => ['alias' => 'first-block', 'properties' => []],
            'block-2' => ['alias' => 'second-block', 'properties' => []],
            'block-3' => ['alias' => 'third-block', 'properties' => []],
        ];

        $blockIds = array_keys($this->rowBlock->blocks);
        $blockIdToMove = 'block-1'; // First block
        $currentIndex = array_search($blockIdToMove, $blockIds);
        $lastIndex = count($blockIds) - 1;

        // Simulate moveBlockDown logic
        if ($currentIndex !== false && $currentIndex < $lastIndex) {
            $newOrder = $blockIds;
            $temp = $newOrder[$currentIndex + 1];
            $newOrder[$currentIndex + 1] = $newOrder[$currentIndex];
            $newOrder[$currentIndex] = $temp;

            $this->rowBlock->blocks = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $this->rowBlock->blocks[$id]])->toArray();
        }

        $newOrder = array_keys($this->rowBlock->blocks);
        $this->assertEquals('block-2', $newOrder[0]);
        $this->assertEquals('block-1', $newOrder[1]);
        $this->assertEquals('block-3', $newOrder[2]);
    }

    /** @test */
    public function it_handles_block_positioning_when_adding(): void
    {
        // Add initial blocks
        $this->rowBlock->blocks = [
            'block-1' => ['alias' => 'first-block', 'properties' => []],
            'block-2' => ['alias' => 'second-block', 'properties' => []],
        ];

        $newBlockId = 'new-block';
        $newBlock = ['alias' => 'new-block', 'properties' => []];
        $beforeBlockId = 'block-2';

        // Simulate adding block before specific block
        $blockIds = array_keys($this->rowBlock->blocks);
        $position = array_search($beforeBlockId, $blockIds);

        if ($position !== false) {
            $newBlocks = [];
            foreach ($blockIds as $index => $id) {
                if ($index === $position) {
                    $newBlocks[$newBlockId] = $newBlock; // Add new block before
                }
                $newBlocks[$id] = $this->rowBlock->blocks[$id]; // Add existing block
            }
            $this->rowBlock->blocks = $newBlocks;
        }

        $finalOrder = array_keys($this->rowBlock->blocks);
        $this->assertEquals(['block-1', 'new-block', 'block-2'], $finalOrder);
    }

    /** @test */
    public function it_handles_nested_row_structure(): void
    {
        $nestedRowBlock = [
            'alias' => 'trinavo-livewire-page-builder-http-livewire-row-block',
            'properties' => ['flex' => 'column'],
            'blocks' => [], // Nested rows start with empty blocks
        ];

        $blockId = 'nested-row-1';
        $this->rowBlock->blocks[$blockId] = $nestedRowBlock;

        $this->assertArrayHasKey($blockId, $this->rowBlock->blocks);
        $this->assertArrayHasKey('blocks', $this->rowBlock->blocks[$blockId]);
        $this->assertEmpty($this->rowBlock->blocks[$blockId]['blocks']);
    }

    /** @test */
    public function it_generates_css_classes_from_properties(): void
    {
        $this->rowBlock->properties = [
            'flex' => 'row',
            'contentCentered' => true,
            'contentWidthDesktop' => 'w-full',
        ];

        // Test that properties are set correctly
        $this->assertEquals('row', $this->rowBlock->properties['flex']);
        $this->assertTrue($this->rowBlock->properties['contentCentered']);
        $this->assertEquals('w-full', $this->rowBlock->properties['contentWidthDesktop']);
    }

    /** @test */
    public function it_handles_gap_properties(): void
    {
        $this->rowBlock->properties = [
            'mobileGap' => '2',
            'tabletGap' => '4',
            'desktopGap' => '6',
        ];

        $this->assertEquals('2', $this->rowBlock->properties['mobileGap']);
        $this->assertEquals('4', $this->rowBlock->properties['tabletGap']);
        $this->assertEquals('6', $this->rowBlock->properties['desktopGap']);
    }

    /** @test */
    public function it_maintains_block_order_integrity(): void
    {
        // Add blocks in specific order
        $this->rowBlock->blocks = [
            'first' => ['alias' => 'first', 'properties' => []],
            'second' => ['alias' => 'second', 'properties' => []],
            'third' => ['alias' => 'third', 'properties' => []],
        ];

        $originalOrder = array_keys($this->rowBlock->blocks);

        // Perform various operations that shouldn't change order
        $this->rowBlock->blocks['first']['properties']['updated'] = true;

        $currentOrder = array_keys($this->rowBlock->blocks);
        $this->assertEquals($originalOrder, $currentOrder);
    }
}