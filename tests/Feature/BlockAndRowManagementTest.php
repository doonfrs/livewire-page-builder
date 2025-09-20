<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class BlockAndRowManagementTest extends TestCase
{
    protected Theme $theme;
    protected BuilderPage $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->theme = Theme::create([
            'name' => 'Test Theme',
            'description' => 'Test theme for block and row management',
        ]);

        $this->page = BuilderPage::create([
            'key' => 'test-page',
            'theme_id' => $this->theme->id,
            'components' => [],
        ]);
    }

    protected function createPageEditorComponent()
    {
        // Mock the config for the service to work properly in package testing
        config()->set('page-builder.blocks', [
            \Trinavo\LivewirePageBuilder\Blocks\RichText::class,
            \Trinavo\LivewirePageBuilder\Blocks\Section::class,
        ]);
        config()->set('page-builder.pages', []);

        // Use the actual service to get available blocks
        $service = app(\Trinavo\LivewirePageBuilder\Services\PageBuilderService::class);
        $availableBlocks = $service->getAvailableBlocks();

        // Debug: Let's see what aliases are generated
        // dump(collect($availableBlocks)->pluck('alias')->toArray());

        $component = Livewire::test(PageEditor::class);
        return $component
            ->set('pageKey', 'test-page')
            ->set('themeId', $this->theme->id)
            ->set('page', $this->page)
            ->set('rows', [])
            ->set('availableBlocks', $availableBlocks)
            ->set('availableThemes', []);
    }

    /** @test */
    public function can_add_block_to_row_and_delete_it(): void
    {
        $component = $this->createPageEditorComponent();

        // Add a new row
        $component->call('addRow');

        // Get the row ID (it should be the first and only row)
        $rowId = array_key_first($component->get('rows'));
        $this->assertNotNull($rowId, 'Row should be created');

        // Add a block to the row (using the RichText block alias)
        $component->call('addBlockToRow', $rowId, 'page-builder-trinavo-livewire-page-builder-blocks-rich-text');

        // Verify block was added
        $rows = $component->get('rows');
        $blocks = $rows[$rowId]['blocks'] ?? [];
        $this->assertCount(1, $blocks, 'Block should be added to row');

        $blockId = array_key_first($blocks);
        $this->assertNotNull($blockId, 'Block ID should exist');

        // Delete the block using the deleteBlock method (simulating user clicking delete)
        $component->call('deleteBlock', $blockId);

        // Verify block was deleted
        $rows = $component->get('rows');
        $blocks = $rows[$rowId]['blocks'] ?? [];
        $this->assertCount(0, $blocks, 'Block should be deleted from row');
    }

    /** @test */
    public function can_add_multiple_blocks_and_delete_specific_one(): void
    {
        $component = $this->createPageEditorComponent();

        // Add a row
        $component->call('addRow');
        $rowId = array_key_first($component->get('rows'));

        // Add multiple blocks
        $component->call('addBlockToRow', $rowId, 'page-builder-trinavo-livewire-page-builder-blocks-rich-text');
        $component->call('addBlockToRow', $rowId, 'page-builder-trinavo-livewire-page-builder-blocks-section');
        $component->call('addBlockToRow', $rowId, 'page-builder-trinavo-livewire-page-builder-blocks-rich-text');

        // Verify 3 blocks were added
        $rows = $component->get('rows');
        $blocks = $rows[$rowId]['blocks'] ?? [];
        $this->assertCount(3, $blocks, 'Should have 3 blocks in row');

        // Get block IDs
        $blockIds = array_keys($blocks);
        $secondBlockId = $blockIds[1];

        // Delete the middle block
        $component->call('deleteBlock', $secondBlockId);

        // Verify only the specific block was deleted
        $rows = $component->get('rows');
        $blocks = $rows[$rowId]['blocks'] ?? [];
        $this->assertCount(2, $blocks, 'Should have 2 blocks after deletion');
        $this->assertArrayNotHasKey($secondBlockId, $blocks, 'Specific block should be deleted');
        $this->assertArrayHasKey($blockIds[0], $blocks, 'First block should remain');
        $this->assertArrayHasKey($blockIds[2], $blocks, 'Third block should remain');
    }

    /** @test */
    public function can_add_multiple_rows_with_blocks_and_delete_row(): void
    {
        $component = $this->createPageEditorComponent();

        // Add first row with blocks
        $component->call('addRow');
        $firstRowId = array_key_first($component->get('rows'));
        $component->call('addBlockToRow', $firstRowId, 'page-builder-trinavo-livewire-page-builder-blocks-rich-text');
        $component->call('addBlockToRow', $firstRowId, 'page-builder-trinavo-livewire-page-builder-blocks-section');

        // Add second row with blocks
        $component->call('addRow');
        $rows = $component->get('rows');
        $rowIds = array_keys($rows);
        $secondRowId = $rowIds[1];
        $component->call('addBlockToRow', $secondRowId, 'page-builder-trinavo-livewire-page-builder-blocks-rich-text');

        // Add third row with blocks
        $component->call('addRow');
        $rows = $component->get('rows');
        $rowIds = array_keys($rows);
        $thirdRowId = $rowIds[2];
        $component->call('addBlockToRow', $thirdRowId, 'page-builder-trinavo-livewire-page-builder-blocks-section');
        $component->call('addBlockToRow', $thirdRowId, 'page-builder-trinavo-livewire-page-builder-blocks-rich-text');

        // Verify we have 3 rows
        $rows = $component->get('rows');
        $this->assertCount(3, $rows, 'Should have 3 rows');

        // Delete the second row
        $component->call('deleteRow', $secondRowId);

        // Verify the row was deleted
        $rows = $component->get('rows');
        $this->assertCount(2, $rows, 'Should have 2 rows after deletion');
        $this->assertArrayNotHasKey($secondRowId, $rows, 'Second row should be deleted');
        $this->assertArrayHasKey($firstRowId, $rows, 'First row should remain');
        $this->assertArrayHasKey($thirdRowId, $rows, 'Third row should remain');

        // Verify the blocks in remaining rows are intact
        $this->assertCount(2, $rows[$firstRowId]['blocks'], 'First row should still have 2 blocks');
        $this->assertCount(2, $rows[$thirdRowId]['blocks'], 'Third row should still have 2 blocks');
    }

    /** @test */
    public function deleted_rows_and_blocks_are_not_saved(): void
    {
        $component = $this->createPageEditorComponent();

        // Add multiple rows with blocks
        $component->call('addRow');
        $firstRowId = array_key_first($component->get('rows'));
        $component->call('addBlockToRow', $firstRowId, 'page-builder-trinavo-livewire-page-builder-blocks-rich-text');
        $component->call('addBlockToRow', $firstRowId, 'page-builder-trinavo-livewire-page-builder-blocks-section');

        $component->call('addRow');
        $rows = $component->get('rows');
        $rowIds = array_keys($rows);
        $secondRowId = $rowIds[1];
        $component->call('addBlockToRow', $secondRowId, 'page-builder-trinavo-livewire-page-builder-blocks-rich-text');

        $component->call('addRow');
        $rows = $component->get('rows');
        $rowIds = array_keys($rows);
        $thirdRowId = $rowIds[2];
        $component->call('addBlockToRow', $thirdRowId, 'page-builder-trinavo-livewire-page-builder-blocks-section');

        // Delete second row
        $component->call('deleteRow', $secondRowId);

        // Delete a block from the first row
        $rows = $component->get('rows');
        $firstRowBlocks = array_keys($rows[$firstRowId]['blocks']);
        $component->call('deleteBlock', $firstRowBlocks[0]);

        // Save the page
        $component->call('savePage');

        // Reload the page to verify saved data
        $savedPage = BuilderPage::where('key', 'test-page')
            ->where('theme_id', $this->theme->id)
            ->first();

        $savedComponents = $savedPage->components;

        // Verify only 2 rows are saved (not 3)
        $this->assertCount(2, $savedComponents, 'Should save only 2 rows');

        // Verify the deleted row is not in saved data
        $savedRowIds = array_keys($savedComponents);
        $this->assertNotContains($secondRowId, $savedRowIds, 'Deleted row should not be saved');

        // Verify the first row has only 1 block (not 2)
        $firstRowData = $savedComponents[$firstRowId] ?? null;
        $this->assertNotNull($firstRowData, 'First row should be saved');
        $this->assertCount(1, $firstRowData['blocks'], 'First row should have only 1 block after deletion');

        // Verify the third row has its block
        $thirdRowData = $savedComponents[$thirdRowId] ?? null;
        $this->assertNotNull($thirdRowData, 'Third row should be saved');
        $this->assertCount(1, $thirdRowData['blocks'], 'Third row should have its block');
    }

    /** @test */
    public function can_delete_all_blocks_from_row_and_row_remains(): void
    {
        $component = $this->createPageEditorComponent();

        // Add a row with multiple blocks
        $component->call('addRow');
        $rowId = array_key_first($component->get('rows'));

        $component->call('addBlockToRow', $rowId, 'page-builder-trinavo-livewire-page-builder-blocks-rich-text');
        $component->call('addBlockToRow', $rowId, 'page-builder-trinavo-livewire-page-builder-blocks-section');
        $component->call('addBlockToRow', $rowId, 'page-builder-trinavo-livewire-page-builder-blocks-rich-text');

        // Get all block IDs
        $rows = $component->get('rows');
        $blockIds = array_keys($rows[$rowId]['blocks']);

        // Delete all blocks one by one
        foreach ($blockIds as $blockId) {
            $component->call('deleteBlock', $blockId);
        }

        // Verify row still exists but has no blocks
        $rows = $component->get('rows');
        $this->assertArrayHasKey($rowId, $rows, 'Row should still exist');
        $this->assertCount(0, $rows[$rowId]['blocks'], 'Row should have no blocks');

        // Save and verify
        $component->call('savePage');

        $savedPage = BuilderPage::where('key', 'test-page')
            ->where('theme_id', $this->theme->id)
            ->first();

        $savedComponents = $savedPage->components;
        $this->assertArrayHasKey($rowId, $savedComponents, 'Empty row should be saved');
        $this->assertCount(0, $savedComponents[$rowId]['blocks'], 'Saved row should have no blocks');
    }

    /** @test */
    public function can_handle_complex_row_and_block_operations(): void
    {
        $component = $this->createPageEditorComponent();

        // Create a complex structure
        $component->call('addRow');
        $row1Id = array_key_first($component->get('rows'));
        $component->call('addBlockToRow', $row1Id, 'page-builder-trinavo-livewire-page-builder-blocks-rich-text');
        $component->call('addBlockToRow', $row1Id, 'page-builder-trinavo-livewire-page-builder-blocks-section');

        $component->call('addRow');
        $rows = $component->get('rows');
        $row2Id = array_keys($rows)[1];
        $component->call('addBlockToRow', $row2Id, 'page-builder-trinavo-livewire-page-builder-blocks-rich-text');

        $component->call('addRow');
        $rows = $component->get('rows');
        $row3Id = array_keys($rows)[2];
        $component->call('addBlockToRow', $row3Id, 'page-builder-trinavo-livewire-page-builder-blocks-section');

        // Move rows around
        $component->call('moveRowDown', $row1Id);
        $component->call('moveRowUp', $row3Id);

        // Delete some blocks
        $rows = $component->get('rows');
        $row1Blocks = array_keys($rows[$row1Id]['blocks']);
        $component->call('deleteBlock', $row1Blocks[0]);

        // Add more blocks after deletion
        $component->call('addBlockToRow', $row2Id, 'page-builder-trinavo-livewire-page-builder-blocks-section');
        $component->call('addBlockToRow', $row3Id, 'page-builder-trinavo-livewire-page-builder-blocks-rich-text');

        // Delete a row
        $component->call('deleteRow', $row2Id);

        // Save and verify structure
        $component->call('savePage');

        $savedPage = BuilderPage::where('key', 'test-page')
            ->where('theme_id', $this->theme->id)
            ->first();

        $savedComponents = $savedPage->components;

        // Should have 2 rows (row1 and row3, not row2)
        $this->assertCount(2, $savedComponents, 'Should have 2 rows after operations');
        $this->assertArrayHasKey($row1Id, $savedComponents, 'Row 1 should exist');
        $this->assertArrayHasKey($row3Id, $savedComponents, 'Row 3 should exist');
        $this->assertArrayNotHasKey($row2Id, $savedComponents, 'Row 2 should be deleted');

        // Verify block counts
        $this->assertCount(1, $savedComponents[$row1Id]['blocks'], 'Row 1 should have 1 block');
        $this->assertCount(2, $savedComponents[$row3Id]['blocks'], 'Row 3 should have 2 blocks');
    }

    /** @test */
    public function dispatch_events_work_for_block_and_row_deletion(): void
    {
        $component = $this->createPageEditorComponent();

        // Add rows and blocks
        $component->call('addRow');
        $rowId = array_key_first($component->get('rows'));
        $component->call('addBlockToRow', $rowId, 'page-builder-trinavo-livewire-page-builder-blocks-rich-text');

        $rows = $component->get('rows');
        $blockId = array_key_first($rows[$rowId]['blocks']);

        // Test deleteBlock event dispatch
        $component->dispatch('deleteBlock', blockId: $blockId);

        $rows = $component->get('rows');
        $this->assertCount(0, $rows[$rowId]['blocks'], 'Block should be deleted via event');

        // Add another block and test deleteRow event
        $component->call('addBlockToRow', $rowId, 'page-builder-trinavo-livewire-page-builder-blocks-section');
        $component->dispatch('deleteRow', rowId: $rowId);

        $rows = $component->get('rows');
        $this->assertCount(0, $rows, 'Row should be deleted via event');
    }
}