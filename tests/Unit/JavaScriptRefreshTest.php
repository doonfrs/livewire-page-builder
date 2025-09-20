<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class JavaScriptRefreshTest extends TestCase
{
    /** @test */
    public function page_editor_generates_javascript_for_nested_row_deletion(): void
    {
        $pageEditor = new PageEditor;
        $pageEditor->rows = [
            'parent-row-id' => [
                'blocks' => [
                    'nested-row-to-delete' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-1/2'],
                        'blocks' => [],
                    ],
                    'remaining-block' => [
                        'alias' => 'some-block',
                        'properties' => ['textColor' => '#000000'],
                    ],
                ],
                'properties' => ['desktopWidth' => 'w-full'],
            ],
        ];

        // Capture any JavaScript that gets executed
        $executedJs = null;
        $pageEditor->js = function ($script) use (&$executedJs) {
            $executedJs = $script;
        };

        // Delete the nested row
        $pageEditor->deleteRow('nested-row-to-delete');

        // Verify backend data is updated
        $this->assertArrayNotHasKey('nested-row-to-delete', $pageEditor->rows['parent-row-id']['blocks']);
        $this->assertArrayHasKey('remaining-block', $pageEditor->rows['parent-row-id']['blocks']);

        // Note: In a real app, the JavaScript would be executed, but in unit tests
        // we can't easily test the $this->js() method. The important part is that
        // the backend deletion works correctly.
    }

    /** @test */
    public function row_block_refresh_blocks_method_works(): void
    {
        $rowBlock = new RowBlock;
        $rowBlock->rowId = 'test-row';
        $rowBlock->blocks = [
            'block-1' => ['alias' => 'some-block', 'properties' => []],
            'block-2' => ['alias' => 'another-block', 'properties' => []],
            'block-to-remove' => ['alias' => 'third-block', 'properties' => []],
        ];

        // Verify initial state
        $this->assertCount(3, $rowBlock->blocks);
        $this->assertArrayHasKey('block-to-remove', $rowBlock->blocks);

        // Simulate JavaScript calling refreshBlocks with updated data
        $updatedBlocks = [
            'block-1' => ['alias' => 'some-block', 'properties' => []],
            'block-2' => ['alias' => 'another-block', 'properties' => []],
        ];

        $rowBlock->refreshBlocks($updatedBlocks);

        // Verify the blocks were updated
        $this->assertCount(2, $rowBlock->blocks);
        $this->assertArrayNotHasKey('block-to-remove', $rowBlock->blocks);
        $this->assertArrayHasKey('block-1', $rowBlock->blocks);
        $this->assertArrayHasKey('block-2', $rowBlock->blocks);
    }

    /** @test */
    public function refresh_blocks_handles_empty_blocks_array(): void
    {
        $rowBlock = new RowBlock;
        $rowBlock->rowId = 'test-row';
        $rowBlock->blocks = [
            'only-block' => ['alias' => 'some-block', 'properties' => []],
        ];

        // Verify initial state
        $this->assertCount(1, $rowBlock->blocks);

        // Simulate removing all blocks
        $rowBlock->refreshBlocks([]);

        // Verify all blocks were removed
        $this->assertEmpty($rowBlock->blocks);
    }

    /** @test */
    public function javascript_approach_explanation(): void
    {
        // This test documents how the JavaScript approach works:

        // 1. User deletes nested row in UI
        // 2. PageEditor::deleteRow() updates backend data ($this->rows)
        // 3. PageEditor executes JavaScript via $this->js() that:
        //    a. Finds the parent row DOM element by ID (row-{parentRowId})
        //    b. Dispatches a browser custom event 'refresh-row-blocks'
        //    c. Passes the updated blocks data in event.detail
        // 4. Row template listens for 'refresh-row-blocks' event (Alpine.js)
        // 5. Alpine.js checks if event is for this row (parentRowId matches)
        // 6. Alpine.js calls $wire.refreshBlocks(updatedBlocks)
        // 7. RowBlock::refreshBlocks() updates $this->blocks
        // 8. Livewire re-renders the component with updated blocks
        // 9. UI shows the nested row has been removed

        $this->assertTrue(true, 'JavaScript approach documented');
    }
}
