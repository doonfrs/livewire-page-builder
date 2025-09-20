<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit\Http\Livewire;

use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class PageEditorTest extends TestCase
{
    protected PageEditor $pageEditor;
    protected Theme $theme;
    protected BuilderPage $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->theme = Theme::create([
            'name' => 'Test Theme',
            'description' => 'Test theme for unit tests',
        ]);

        $this->page = BuilderPage::create([
            'key' => 'test-page',
            'theme_id' => $this->theme->id,
            'components' => [],
        ]);

        $this->pageEditor = new PageEditor();
        $this->pageEditor->pageKey = 'test-page';
        $this->pageEditor->themeId = $this->theme->id;
        $this->pageEditor->page = $this->page;
        $this->pageEditor->rows = [];
    }

    /** @test */
    public function it_can_add_a_new_row(): void
    {
        $initialRowCount = count($this->pageEditor->rows);

        $this->pageEditor->addRow();

        $this->assertCount($initialRowCount + 1, $this->pageEditor->rows);

        $rows = $this->pageEditor->rows;
        $lastRow = end($rows);

        $this->assertArrayHasKey('blocks', $lastRow);
        $this->assertArrayHasKey('properties', $lastRow);
        $this->assertEmpty($lastRow['blocks']);
        $this->assertIsArray($lastRow['properties']);
    }

    /** @test */
    public function it_adds_row_after_specific_row(): void
    {
        // Add first row
        $this->pageEditor->addRow();
        $firstRowId = array_keys($this->pageEditor->rows)[0];

        // Add second row
        $this->pageEditor->addRow();
        $secondRowId = array_keys($this->pageEditor->rows)[1];

        // Add row after first row
        $this->pageEditor->addRow($firstRowId);

        $this->assertCount(3, $this->pageEditor->rows);

        $rowIds = array_keys($this->pageEditor->rows);
        $this->assertEquals($firstRowId, $rowIds[0]);
        $this->assertEquals($secondRowId, $rowIds[2]);

        // The new row should be at index 1 (between first and second)
        $this->assertNotEquals($firstRowId, $rowIds[1]);
        $this->assertNotEquals($secondRowId, $rowIds[1]);
    }

    /** @test */
    public function it_adds_row_before_specific_row(): void
    {
        // Add first row
        $this->pageEditor->addRow();
        $firstRowId = array_keys($this->pageEditor->rows)[0];

        // Add row before first row
        $this->pageEditor->addRow(null, $firstRowId);

        $this->assertCount(2, $this->pageEditor->rows);

        $rowIds = array_keys($this->pageEditor->rows);
        $this->assertEquals($firstRowId, $rowIds[1]);

        // The new row should be at index 0 (before first row)
        $this->assertNotEquals($firstRowId, $rowIds[0]);
    }

    /** @test */
    public function it_can_delete_row(): void
    {
        // Add a row
        $this->pageEditor->addRow();
        $rowId = array_keys($this->pageEditor->rows)[0];

        $this->assertCount(1, $this->pageEditor->rows);

        // Delete the row
        $this->pageEditor->deleteRow($rowId);

        $this->assertCount(0, $this->pageEditor->rows);
    }

    /** @test */
    public function it_can_move_row_up(): void
    {
        // Add two rows
        $this->pageEditor->addRow();
        $this->pageEditor->addRow();

        $rowIds = array_keys($this->pageEditor->rows);
        $firstRowId = $rowIds[0];
        $secondRowId = $rowIds[1];

        // Move second row up
        $this->pageEditor->moveRowUp($secondRowId);

        $updatedRowIds = array_keys($this->pageEditor->rows);

        $this->assertEquals($secondRowId, $updatedRowIds[0]);
        $this->assertEquals($firstRowId, $updatedRowIds[1]);
    }

    /** @test */
    public function it_can_move_row_down(): void
    {
        // Add two rows
        $this->pageEditor->addRow();
        $this->pageEditor->addRow();

        $rowIds = array_keys($this->pageEditor->rows);
        $firstRowId = $rowIds[0];
        $secondRowId = $rowIds[1];

        // Move first row down
        $this->pageEditor->moveRowDown($firstRowId);

        $updatedRowIds = array_keys($this->pageEditor->rows);

        $this->assertEquals($secondRowId, $updatedRowIds[0]);
        $this->assertEquals($firstRowId, $updatedRowIds[1]);
    }

    /** @test */
    public function it_does_not_move_first_row_up(): void
    {
        // Add a row
        $this->pageEditor->addRow();

        $originalOrder = array_keys($this->pageEditor->rows);
        $firstRowId = $originalOrder[0];

        // Try to move first row up (should not move)
        $this->pageEditor->moveRowUp($firstRowId);

        $updatedOrder = array_keys($this->pageEditor->rows);

        $this->assertEquals($originalOrder, $updatedOrder);
    }

    /** @test */
    public function it_does_not_move_last_row_down(): void
    {
        // Add two rows
        $this->pageEditor->addRow();
        $this->pageEditor->addRow();

        $originalOrder = array_keys($this->pageEditor->rows);
        $lastRowId = end($originalOrder);

        // Try to move last row down (should not move)
        $this->pageEditor->moveRowDown($lastRowId);

        $updatedOrder = array_keys($this->pageEditor->rows);

        $this->assertEquals($originalOrder, $updatedOrder);
    }

    /** @test */
    public function it_can_save_page(): void
    {
        // Add some rows
        $this->pageEditor->addRow();
        $this->pageEditor->addRow();

        $this->assertCount(2, $this->pageEditor->rows);

        // Save the page
        $this->pageEditor->savePage();

        // Refresh the page from database
        $this->page->refresh();

        $this->assertIsArray($this->page->components);
        $this->assertCount(2, $this->page->components);
        $this->assertEquals($this->pageEditor->rows, $this->page->components);
    }

    /** @test */
    public function it_can_update_block_property(): void
    {
        // Add a row
        $this->pageEditor->addRow();
        $rowId = array_keys($this->pageEditor->rows)[0];

        // Update row property
        $this->pageEditor->updateBlockProperty($rowId, null, 'flex', 'column');

        $this->assertEquals('column', $this->pageEditor->rows[$rowId]['properties']['flex']);
    }

    /** @test */
    public function it_can_add_block_to_row(): void
    {
        // Add a row
        $this->pageEditor->addRow();
        $rowId = array_keys($this->pageEditor->rows)[0];

        // Mock available blocks to prevent service dependencies
        $this->pageEditor->availableBlocks = [
            [
                'alias' => 'test-block',
                'class' => RowBlock::class,
                'label' => 'Test Block',
            ],
        ];

        // Add block to row
        $this->pageEditor->addBlockToRow($rowId, 'test-block');

        $this->assertCount(1, $this->pageEditor->rows[$rowId]['blocks']);

        $blocks = $this->pageEditor->rows[$rowId]['blocks'];
        $block = end($blocks);

        $this->assertEquals('test-block', $block['alias']);
        $this->assertArrayHasKey('properties', $block);
    }

    /** @test */
    public function it_preserves_data_integrity_during_operations(): void
    {
        // Add complex structure
        $this->pageEditor->addRow();
        $rowId = array_keys($this->pageEditor->rows)[0];

        // Mock available blocks
        $this->pageEditor->availableBlocks = [
            [
                'alias' => 'test-block',
                'class' => RowBlock::class,
                'label' => 'Test Block',
            ],
        ];

        // Add block
        $this->pageEditor->addBlockToRow($rowId, 'test-block');

        // Update properties
        $this->pageEditor->updateBlockProperty($rowId, null, 'flex', 'row-reverse');

        $originalRows = $this->pageEditor->rows;

        // Save
        $this->pageEditor->savePage();

        // Verify the component's state hasn't changed after save
        $this->assertEquals($originalRows, $this->pageEditor->rows);

        // Verify database matches component state
        $this->page->refresh();
        $this->assertEquals($originalRows, $this->page->components);
    }

    /** @test */
    public function it_handles_nested_row_data_sync(): void
    {
        $nestedRowId = 'nested-row-1';
        $blocks = [
            'block-1' => ['alias' => 'test-block', 'properties' => []],
            'block-2' => ['alias' => 'test-block-2', 'properties' => []],
        ];

        // Create a structure with nested rows
        $this->pageEditor->rows = [
            'row-1' => [
                'blocks' => [
                    $nestedRowId => [
                        'alias' => 'row-block',
                        'properties' => [],
                        'blocks' => ['old-block' => ['alias' => 'old']],
                    ],
                ],
                'properties' => [],
            ],
        ];

        // Sync nested row data
        $this->pageEditor->syncNestedRowData($nestedRowId, $blocks);

        $nestedBlocks = $this->pageEditor->rows['row-1']['blocks'][$nestedRowId]['blocks'];
        $this->assertEquals($blocks, $nestedBlocks);
    }
}