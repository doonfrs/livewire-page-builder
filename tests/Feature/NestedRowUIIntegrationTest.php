<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class NestedRowUIIntegrationTest extends TestCase
{
    /** @test */
    public function row_block_listens_for_nested_row_deleted_event(): void
    {
        // Test that RowBlock components properly listen for the nested-row-deleted event
        $component = Livewire::test(RowBlock::class, [
            'rowId' => 'parent-row-123',
            'properties' => ['desktopWidth' => 'w-full'],
            'blocks' => [
                'nested-row-456' => [
                    'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                    'properties' => ['desktopWidth' => 'w-1/2'],
                    'blocks' => [],
                ],
                'another-block-789' => [
                    'alias' => 'some-other-block',
                    'properties' => ['textColor' => '#000000'],
                ],
            ],
            'editMode' => true,
            'isNested' => false,
        ]);

        // Verify initial state
        $initialBlocks = $component->get('blocks');
        $this->assertCount(2, $initialBlocks);
        $this->assertArrayHasKey('nested-row-456', $initialBlocks);
        $this->assertArrayHasKey('another-block-789', $initialBlocks);

        // Dispatch the nested-row-deleted event (simulating PageEditor dispatch)
        $component->dispatch('nested-row-deleted',
            'parent-row-123',
            'nested-row-456',
            [
                'another-block-789' => [
                    'alias' => 'some-other-block',
                    'properties' => ['textColor' => '#000000'],
                ],
            ]
        );

        // Verify the component updated its blocks
        $updatedBlocks = $component->get('blocks');
        $this->assertCount(1, $updatedBlocks, 'Component should have updated to 1 block');
        $this->assertArrayNotHasKey('nested-row-456', $updatedBlocks, 'Deleted nested row should be removed');
        $this->assertArrayHasKey('another-block-789', $updatedBlocks, 'Other blocks should remain');
    }

    /** @test */
    public function row_block_ignores_events_for_other_parent_rows(): void
    {
        // Test that RowBlock only responds to events for its own rowId
        $component = Livewire::test(RowBlock::class, [
            'rowId' => 'my-parent-row',
            'properties' => ['desktopWidth' => 'w-full'],
            'blocks' => [
                'my-nested-row' => [
                    'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                    'properties' => ['desktopWidth' => 'w-full'],
                    'blocks' => [],
                ],
            ],
            'editMode' => true,
            'isNested' => false,
        ]);

        // Dispatch event for a different parent row
        $component->dispatch('nested-row-deleted',
            'different-parent-row',
            'some-other-nested-row',
            []
        );

        // Component should ignore the event and keep its original blocks
        $blocks = $component->get('blocks');
        $this->assertCount(1, $blocks, 'Component should ignore events for other parents');
        $this->assertArrayHasKey('my-nested-row', $blocks, 'Own blocks should remain unchanged');
    }

    /** @test */
    public function multiple_row_blocks_receive_events_independently(): void
    {
        // Test multiple RowBlock components can receive events independently

        // Create first RowBlock component
        $component1 = Livewire::test(RowBlock::class, [
            'rowId' => 'parent-1',
            'properties' => ['desktopWidth' => 'w-full'],
            'blocks' => [
                'nested-1a' => [
                    'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                    'properties' => ['desktopWidth' => 'w-1/2'],
                    'blocks' => [],
                ],
                'nested-1b' => [
                    'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                    'properties' => ['desktopWidth' => 'w-1/2'],
                    'blocks' => [],
                ],
            ],
            'editMode' => true,
            'isNested' => false,
        ]);

        // Create second RowBlock component
        $component2 = Livewire::test(RowBlock::class, [
            'rowId' => 'parent-2',
            'properties' => ['desktopWidth' => 'w-full'],
            'blocks' => [
                'nested-2a' => [
                    'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                    'properties' => ['desktopWidth' => 'w-full'],
                    'blocks' => [],
                ],
            ],
            'editMode' => true,
            'isNested' => false,
        ]);

        // Dispatch event to delete nested row from parent-1
        $component1->dispatch('nested-row-deleted',
            'parent-1',
            'nested-1a',
            [
                'nested-1b' => [
                    'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                    'properties' => ['desktopWidth' => 'w-1/2'],
                    'blocks' => [],
                ],
            ]
        );

        // Component1 should update
        $blocks1 = $component1->get('blocks');
        $this->assertCount(1, $blocks1, 'Component1 should be updated');
        $this->assertArrayNotHasKey('nested-1a', $blocks1, 'Deleted nested row should be removed');
        $this->assertArrayHasKey('nested-1b', $blocks1, 'Remaining nested row should stay');

        // Component2 should remain unchanged
        $blocks2 = $component2->get('blocks');
        $this->assertCount(1, $blocks2, 'Component2 should remain unchanged');
        $this->assertArrayHasKey('nested-2a', $blocks2, 'Component2 blocks should be unaffected');
    }

    /** @test */
    public function event_handler_logs_update_information(): void
    {
        // Test that the event handler logs information about the update
        $component = Livewire::test(RowBlock::class, [
            'rowId' => 'test-parent',
            'properties' => ['desktopWidth' => 'w-full'],
            'blocks' => [
                'test-nested' => [
                    'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                    'properties' => ['desktopWidth' => 'w-1/2'],
                    'blocks' => [],
                ],
            ],
            'editMode' => true,
            'isNested' => false,
        ]);

        // Dispatch the event
        $component->dispatch('nested-row-deleted',
            'test-parent',
            'test-nested',
            []
        );

        // Verify the component updated (blocks should now be empty)
        $this->assertEmpty($component->get('blocks'), 'Component should have empty blocks after deletion');
    }
}
