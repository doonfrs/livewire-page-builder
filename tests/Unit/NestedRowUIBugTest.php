<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class NestedRowUIBugTest extends TestCase
{
    /** @test */
    public function demonstrates_ui_synchronization_bug_after_nested_row_deletion(): void
    {
        // Set up PageEditor with nested row structure
        $pageEditor = new PageEditor();
        $pageEditor->rows = [
            'parent-row-id' => [
                'blocks' => [
                    'nested-row-id' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => [
                            'mobileWidth' => 'w-full',
                            'desktopWidth' => 'w-1/2',
                        ],
                        'blocks' => [],
                    ],
                    'another-block-id' => [
                        'alias' => 'some-other-block',
                        'properties' => ['textColor' => '#000000'],
                    ]
                ],
                'properties' => ['desktopWidth' => 'w-full']
            ]
        ];

        // Set up RowBlock component for the parent row (simulates frontend component)
        $parentRowBlock = new RowBlock();
        $parentRowBlock->rowId = 'parent-row-id';
        $parentRowBlock->properties = $pageEditor->rows['parent-row-id']['properties'];
        $parentRowBlock->blocks = $pageEditor->rows['parent-row-id']['blocks'];
        $parentRowBlock->editMode = true;
        $parentRowBlock->isNested = false;

        // Verify initial state - both backend and frontend have the nested row
        $this->assertArrayHasKey('nested-row-id', $pageEditor->rows['parent-row-id']['blocks'],
            'Backend should initially have the nested row');
        $this->assertArrayHasKey('nested-row-id', $parentRowBlock->blocks,
            'Frontend component should initially have the nested row');
        $this->assertCount(2, $pageEditor->rows['parent-row-id']['blocks'],
            'Backend should have 2 blocks initially');
        $this->assertCount(2, $parentRowBlock->blocks,
            'Frontend component should have 2 blocks initially');

        // Delete the nested row through PageEditor (backend operation)
        $pageEditor->deleteRow('nested-row-id');

        // Backend is updated correctly
        $this->assertArrayNotHasKey('nested-row-id', $pageEditor->rows['parent-row-id']['blocks'],
            'Backend should have deleted the nested row');
        $this->assertCount(1, $pageEditor->rows['parent-row-id']['blocks'],
            'Backend should now have 1 block');

        // BUG: Frontend component still has the old data - UI not synchronized
        $this->assertArrayHasKey('nested-row-id', $parentRowBlock->blocks,
            'BUG: Frontend component still contains the deleted nested row');
        $this->assertCount(2, $parentRowBlock->blocks,
            'BUG: Frontend component still shows 2 blocks instead of 1');

        // This demonstrates the core issue: backend data is updated but frontend components
        // don't know about the change and continue to render the old structure
    }

    /** @test */
    public function demonstrates_expected_behavior_with_manual_sync(): void
    {
        // This test shows what should happen - when backend deletes nested row,
        // frontend components should be notified and update their data

        $pageEditor = new PageEditor();
        $pageEditor->rows = [
            'parent-row' => [
                'blocks' => [
                    'nested-to-delete' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-1/3'],
                        'blocks' => [],
                    ]
                ],
                'properties' => ['desktopWidth' => 'w-full']
            ]
        ];

        $parentRowBlock = new RowBlock();
        $parentRowBlock->rowId = 'parent-row';
        $parentRowBlock->properties = $pageEditor->rows['parent-row']['properties'];
        $parentRowBlock->blocks = $pageEditor->rows['parent-row']['blocks'];

        // Delete nested row from backend
        $pageEditor->deleteRow('nested-to-delete');

        // MANUALLY sync the frontend component (this is what should happen automatically)
        $parentRowBlock->blocks = $pageEditor->rows['parent-row']['blocks'];

        // Now both backend and frontend are synchronized
        $this->assertArrayNotHasKey('nested-to-delete', $pageEditor->rows['parent-row']['blocks'],
            'Backend should have deleted nested row');
        $this->assertArrayNotHasKey('nested-to-delete', $parentRowBlock->blocks,
            'Frontend should also have deleted nested row after sync');
        $this->assertEmpty($pageEditor->rows['parent-row']['blocks'],
            'Backend should have empty blocks');
        $this->assertEmpty($parentRowBlock->blocks,
            'Frontend should also have empty blocks after sync');
    }

    /** @test */
    public function demonstrates_multiple_component_instances_problem(): void
    {
        // This test shows that the problem affects all component instances
        // When you have multiple RowBlock components on the page, none of them
        // get updated when PageEditor deletes a nested row

        $pageEditor = new PageEditor();
        $pageEditor->rows = [
            'parent-1' => [
                'blocks' => [
                    'nested-1' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-1/2'],
                        'blocks' => [],
                    ]
                ],
                'properties' => ['desktopWidth' => 'w-full']
            ],
            'parent-2' => [
                'blocks' => [
                    'nested-2' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-1/3'],
                        'blocks' => [],
                    ]
                ],
                'properties' => ['desktopWidth' => 'w-full']
            ]
        ];

        // Create multiple RowBlock component instances (simulating real app)
        $rowBlock1 = new RowBlock();
        $rowBlock1->rowId = 'parent-1';
        $rowBlock1->blocks = $pageEditor->rows['parent-1']['blocks'];

        $rowBlock2 = new RowBlock();
        $rowBlock2->rowId = 'parent-2';
        $rowBlock2->blocks = $pageEditor->rows['parent-2']['blocks'];

        // Delete nested row from parent-1
        $pageEditor->deleteRow('nested-1');

        // Backend is updated
        $this->assertEmpty($pageEditor->rows['parent-1']['blocks'],
            'Backend parent-1 should have empty blocks');
        $this->assertCount(1, $pageEditor->rows['parent-2']['blocks'],
            'Backend parent-2 should still have its nested row');

        // BUG: Frontend components are not updated
        $this->assertCount(1, $rowBlock1->blocks,
            'BUG: RowBlock1 component still shows the deleted nested row');
        $this->assertCount(1, $rowBlock2->blocks,
            'RowBlock2 component correctly shows its nested row (unaffected)');

        // The issue is that RowBlock1 doesn't know that its nested row was deleted
        // from the PageEditor component
    }

    /** @test */
    public function identifies_root_cause_of_ui_sync_issue(): void
    {
        // This test documents the root cause of the UI synchronization issue

        // ROOT CAUSE: Livewire components maintain their own state independently
        // When PageEditor deletes a nested row from $this->rows, it only updates
        // the PageEditor component's state. The RowBlock components that render
        // those nested rows have their own $blocks property and don't automatically
        // know about changes in the PageEditor.

        $pageEditor = new PageEditor();
        $pageEditor->rows = [
            'parent' => [
                'blocks' => ['nested' => ['alias' => 'row-block', 'properties' => [], 'blocks' => []]],
                'properties' => []
            ]
        ];

        $rowBlock = new RowBlock();
        $rowBlock->blocks = ['nested' => ['alias' => 'row-block', 'properties' => [], 'blocks' => []]];

        // These are independent objects with independent state
        $this->assertTrue($pageEditor->rows !== $rowBlock->blocks,
            'PageEditor and RowBlock have independent state objects');

        // When PageEditor changes its state...
        $pageEditor->deleteRow('nested');
        $this->assertEmpty($pageEditor->rows['parent']['blocks']);

        // ...RowBlock state remains unchanged
        $this->assertCount(1, $rowBlock->blocks,
            'RowBlock state is independent and unchanged');

        // SOLUTION NEEDED: PageEditor must notify RowBlock components about changes
        // This can be done through:
        // 1. Livewire events (dispatch/listen)
        // 2. Parent-child component communication
        // 3. Shared state management
        // 4. Component refresh/re-render triggers
    }
}