<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class NestedRowUIFixTest extends TestCase
{
    /** @test */
    public function page_editor_deletes_nested_row_correctly(): void
    {
        $pageEditor = new PageEditor();
        $pageEditor->rows = [
            'parent-row-id' => [
                'blocks' => [
                    'nested-row-id' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-1/2'],
                        'blocks' => [],
                    ],
                    'another-block' => [
                        'alias' => 'some-block',
                        'properties' => ['textColor' => '#000000'],
                    ]
                ],
                'properties' => ['desktopWidth' => 'w-full']
            ]
        ];

        // Delete nested row
        $pageEditor->deleteRow('nested-row-id');

        // Verify the deletion worked
        $this->assertArrayNotHasKey('nested-row-id', $pageEditor->rows['parent-row-id']['blocks']);
        $this->assertArrayHasKey('another-block', $pageEditor->rows['parent-row-id']['blocks']);
        $this->assertCount(1, $pageEditor->rows['parent-row-id']['blocks']);
    }

    /** @test */
    public function row_block_handles_nested_row_deleted_event(): void
    {
        $rowBlock = new RowBlock();
        $rowBlock->rowId = 'parent-row-id';
        $rowBlock->blocks = [
            'nested-row-1' => [
                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                'properties' => ['desktopWidth' => 'w-1/2'],
                'blocks' => [],
            ],
            'nested-row-2' => [
                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                'properties' => ['desktopWidth' => 'w-1/2'],
                'blocks' => [],
            ]
        ];

        // Verify initial state
        $this->assertCount(2, $rowBlock->blocks);
        $this->assertArrayHasKey('nested-row-1', $rowBlock->blocks);

        // Simulate the event from PageEditor
        $updatedBlocks = [
            'nested-row-2' => [
                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                'properties' => ['desktopWidth' => 'w-1/2'],
                'blocks' => [],
            ]
        ];

        $rowBlock->handleNestedRowDeleted('parent-row-id', 'nested-row-1', $updatedBlocks);

        // Verify the RowBlock updated its blocks
        $this->assertCount(1, $rowBlock->blocks, 'RowBlock should have updated blocks');
        $this->assertArrayNotHasKey('nested-row-1', $rowBlock->blocks, 'Deleted nested row should be removed');
        $this->assertArrayHasKey('nested-row-2', $rowBlock->blocks, 'Other nested rows should remain');
    }

    /** @test */
    public function row_block_ignores_event_for_other_parents(): void
    {
        $rowBlock = new RowBlock();
        $rowBlock->rowId = 'my-row-id';
        $rowBlock->blocks = [
            'my-nested-row' => [
                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                'properties' => ['desktopWidth' => 'w-full'],
                'blocks' => [],
            ]
        ];

        // Simulate event for a different parent row
        $rowBlock->handleNestedRowDeleted('different-parent-id', 'some-nested-row', []);

        // RowBlock should ignore the event and keep its blocks unchanged
        $this->assertCount(1, $rowBlock->blocks, 'RowBlock should ignore events for other parents');
        $this->assertArrayHasKey('my-nested-row', $rowBlock->blocks, 'Own nested rows should remain unchanged');
    }

    /** @test */
    public function ui_synchronization_works_with_manual_event_simulation(): void
    {
        // This test simulates the complete flow: PageEditor deletes nested row,
        // then manually triggers the RowBlock event handler to verify UI sync

        // Setup PageEditor
        $pageEditor = new PageEditor();
        $pageEditor->rows = [
            'parent-row' => [
                'blocks' => [
                    'nested-to-delete' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-1/3'],
                        'blocks' => [],
                    ],
                    'nested-to-keep' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-2/3'],
                        'blocks' => [],
                    ]
                ],
                'properties' => ['desktopWidth' => 'w-full']
            ]
        ];

        // Setup RowBlock (simulating the parent row component)
        $parentRowBlock = new RowBlock();
        $parentRowBlock->rowId = 'parent-row';
        $parentRowBlock->blocks = $pageEditor->rows['parent-row']['blocks'];

        // Verify initial state - both have the nested row
        $this->assertArrayHasKey('nested-to-delete', $pageEditor->rows['parent-row']['blocks']);
        $this->assertArrayHasKey('nested-to-delete', $parentRowBlock->blocks);
        $this->assertCount(2, $parentRowBlock->blocks);

        // Delete the nested row through PageEditor
        $pageEditor->deleteRow('nested-to-delete');

        // Verify PageEditor updated its state
        $this->assertArrayNotHasKey('nested-to-delete', $pageEditor->rows['parent-row']['blocks']);

        // Simulate RowBlock receiving the event (what would happen in real app)
        $parentRowBlock->handleNestedRowDeleted(
            'parent-row',
            'nested-to-delete',
            $pageEditor->rows['parent-row']['blocks'] // Updated blocks from PageEditor
        );

        // Verify RowBlock updated its state - UI is now synchronized!
        $this->assertArrayNotHasKey('nested-to-delete', $parentRowBlock->blocks,
            'FIXED: RowBlock should have removed the deleted nested row');
        $this->assertArrayHasKey('nested-to-keep', $parentRowBlock->blocks,
            'Other nested rows should remain');
        $this->assertCount(1, $parentRowBlock->blocks,
            'FIXED: RowBlock should now show correct number of blocks');

        // Both backend and frontend are now synchronized
        $this->assertEquals(
            $pageEditor->rows['parent-row']['blocks'],
            $parentRowBlock->blocks,
            'FIXED: Backend and frontend are now synchronized'
        );
    }
}