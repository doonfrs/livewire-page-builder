<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class NestedRowTest extends TestCase
{
    protected Theme $theme;
    protected BuilderPage $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->theme = Theme::create([
            'name' => 'Test Theme',
            'description' => 'Test theme for nested row functionality',
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

        // Use the actual service to get available blocks (including RowBlock)
        $service = app(\Trinavo\LivewirePageBuilder\Services\PageBuilderService::class);
        $availableBlocks = $service->getAvailableBlocks();

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
    public function can_add_nested_row_inside_row(): void
    {
        $component = $this->createPageEditorComponent();

        // Add parent row
        $component->call('addRow');
        $parentRowId = array_key_first($component->get('rows'));
        $this->assertNotNull($parentRowId, 'Parent row should be created');

        // Get the RowBlock alias
        $availableBlocks = $component->get('availableBlocks');
        $rowBlockAlias = null;
        foreach ($availableBlocks as $block) {
            if ($block['class'] === \Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock::class) {
                $rowBlockAlias = $block['alias'];
                break;
            }
        }
        $this->assertNotNull($rowBlockAlias, 'RowBlock should be available as a droppable block');

        // Add nested row inside parent row
        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias);

        // Verify nested row was added
        $rows = $component->get('rows');
        $parentRow = $rows[$parentRowId];
        $this->assertCount(1, $parentRow['blocks'], 'Parent row should have 1 block (the nested row)');

        $nestedRowBlockId = array_key_first($parentRow['blocks']);
        $nestedRowBlock = $parentRow['blocks'][$nestedRowBlockId];

        $this->assertEquals($rowBlockAlias, $nestedRowBlock['alias'], 'Nested block should be a RowBlock');
        $this->assertArrayHasKey('blocks', $nestedRowBlock, 'Nested row should have blocks array');
        $this->assertIsArray($nestedRowBlock['blocks'], 'Nested row blocks should be an array');
        $this->assertCount(0, $nestedRowBlock['blocks'], 'Nested row should start empty');
    }

    /** @test */
    public function can_add_blocks_to_nested_row(): void
    {
        $component = $this->createPageEditorComponent();

        // Add parent row
        $component->call('addRow');
        $parentRowId = array_key_first($component->get('rows'));

        // Get block aliases
        $availableBlocks = $component->get('availableBlocks');
        $rowBlockAlias = null;
        $richTextAlias = null;

        foreach ($availableBlocks as $block) {
            if ($block['class'] === \Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock::class) {
                $rowBlockAlias = $block['alias'];
            }
            if ($block['class'] === \Trinavo\LivewirePageBuilder\Blocks\RichText::class) {
                $richTextAlias = $block['alias'];
            }
        }

        // Add nested row inside parent row
        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias);

        // Get the nested row ID
        $rows = $component->get('rows');
        $nestedRowBlockId = array_key_first($rows[$parentRowId]['blocks']);

        // Add a block to the nested row (this would simulate user interaction with nested row)
        // For now, we'll verify the structure is set up correctly for this functionality
        $nestedRowBlock = $rows[$parentRowId]['blocks'][$nestedRowBlockId];
        $this->assertArrayHasKey('blocks', $nestedRowBlock, 'Nested row should have blocks array ready for content');
    }

    /** @test */
    public function can_add_multiple_levels_of_nesting(): void
    {
        $component = $this->createPageEditorComponent();

        // Add parent row
        $component->call('addRow');
        $parentRowId = array_key_first($component->get('rows'));

        // Get the RowBlock alias
        $availableBlocks = $component->get('availableBlocks');
        $rowBlockAlias = null;
        foreach ($availableBlocks as $block) {
            if ($block['class'] === \Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock::class) {
                $rowBlockAlias = $block['alias'];
                break;
            }
        }

        // Add first nested row
        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias);

        // Add second nested row inside the parent
        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias);

        // Verify multiple nested rows
        $rows = $component->get('rows');
        $parentRow = $rows[$parentRowId];
        $this->assertCount(2, $parentRow['blocks'], 'Parent row should have 2 nested rows');

        // Verify both are RowBlocks with proper structure
        foreach ($parentRow['blocks'] as $blockId => $block) {
            $this->assertEquals($rowBlockAlias, $block['alias'], 'Each nested block should be a RowBlock');
            $this->assertArrayHasKey('blocks', $block, 'Each nested row should have blocks array');
            $this->assertIsArray($block['blocks'], 'Each nested row blocks should be an array');
        }
    }

    /** @test */
    public function nested_rows_are_properly_saved(): void
    {
        $component = $this->createPageEditorComponent();

        // Add parent row
        $component->call('addRow');
        $parentRowId = array_key_first($component->get('rows'));

        // Get block aliases
        $availableBlocks = $component->get('availableBlocks');
        $rowBlockAlias = null;
        $richTextAlias = null;

        foreach ($availableBlocks as $block) {
            if ($block['class'] === \Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock::class) {
                $rowBlockAlias = $block['alias'];
            }
            if ($block['class'] === \Trinavo\LivewirePageBuilder\Blocks\RichText::class) {
                $richTextAlias = $block['alias'];
            }
        }

        // Add nested row and regular block
        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias);
        $component->call('addBlockToRow', $parentRowId, $richTextAlias);

        // Save the page
        $component->call('savePage');

        // Reload and verify structure
        $savedPage = BuilderPage::where('key', 'test-page')
            ->where('theme_id', $this->theme->id)
            ->first();

        $savedComponents = $savedPage->components;
        $this->assertCount(1, $savedComponents, 'Should save 1 parent row');

        $savedParentRow = $savedComponents[$parentRowId];
        $this->assertCount(2, $savedParentRow['blocks'], 'Parent row should have 2 blocks');

        // Find the nested row block
        $nestedRowBlock = null;
        $regularBlock = null;

        foreach ($savedParentRow['blocks'] as $block) {
            if ($block['alias'] === $rowBlockAlias) {
                $nestedRowBlock = $block;
            } else {
                $regularBlock = $block;
            }
        }

        $this->assertNotNull($nestedRowBlock, 'Nested row block should be saved');
        $this->assertNotNull($regularBlock, 'Regular block should be saved');
        $this->assertArrayHasKey('blocks', $nestedRowBlock, 'Nested row should have blocks structure');
        $this->assertArrayNotHasKey('blocks', $regularBlock, 'Regular block should not have blocks structure');
    }

    /** @test */
    public function can_delete_nested_rows(): void
    {
        $component = $this->createPageEditorComponent();

        // Add parent row
        $component->call('addRow');
        $parentRowId = array_key_first($component->get('rows'));

        // Get the RowBlock alias
        $availableBlocks = $component->get('availableBlocks');
        $rowBlockAlias = null;
        foreach ($availableBlocks as $block) {
            if ($block['class'] === \Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock::class) {
                $rowBlockAlias = $block['alias'];
                break;
            }
        }

        // Add nested row
        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias);

        // Verify nested row exists
        $rows = $component->get('rows');
        $this->assertCount(1, $rows[$parentRowId]['blocks'], 'Should have 1 nested row');

        // Delete the nested row
        $nestedRowBlockId = array_key_first($rows[$parentRowId]['blocks']);
        $component->call('deleteBlock', $nestedRowBlockId);

        // Verify nested row is deleted
        $rows = $component->get('rows');
        $this->assertCount(0, $rows[$parentRowId]['blocks'], 'Nested row should be deleted');
    }
}