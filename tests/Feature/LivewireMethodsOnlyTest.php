<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class LivewireMethodsOnlyTest extends TestCase
{
    protected Theme $theme;

    protected BuilderPage $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->theme = Theme::create([
            'name' => 'Test Theme',
            'description' => 'Test theme for Livewire tests',
        ]);

        $this->page = BuilderPage::create([
            'key' => 'test-page',
            'theme_id' => $this->theme->id,
            'components' => [],
        ]);
    }

    /** @test */
    public function page_editor_livewire_methods_work_without_rendering(): void
    {
        // Test methods without rendering by bypassing the mount/render cycle
        $component = Livewire::test(PageEditor::class);

        // Use the withoutView() method to skip rendering
        $component
            ->set('pageKey', 'test-page')
            ->set('themeId', $this->theme->id)
            ->set('page', $this->page)
            ->set('rows', [])
            ->set('availableBlocks', [])
            ->set('availableThemes', [])
            ->call('addRow')
            ->assertSet('rows', function ($rows) {
                return count($rows) === 1;
            })
            ->assertDispatched('row-added');
    }

    /** @test */
    public function page_editor_livewire_can_save_without_rendering(): void
    {
        $component = Livewire::test(PageEditor::class);

        $component
            ->set('pageKey', 'test-page')
            ->set('themeId', $this->theme->id)
            ->set('page', $this->page)
            ->set('rows', [
                'row-1' => ['blocks' => [], 'properties' => ['flex' => 'row']],
            ])
            ->set('availableBlocks', [])
            ->set('availableThemes', [])
            ->call('savePage');

        // Refresh page from database
        $this->page->refresh();
        $this->assertCount(1, $this->page->components);
        $this->assertEquals('row', $this->page->components['row-1']['properties']['flex']);
    }

    /** @test */
    public function row_block_livewire_methods_work_without_rendering(): void
    {
        // Pass editMode during mount (not via ->set) since it's a locked property
        $component = Livewire::test(RowBlock::class, [
            'rowId' => 'test-row',
            'blocks' => [],
            'properties' => ['flex' => 'row'],
            'editMode' => true,
        ]);

        $component
            ->call('updateBlockProperty', 'test-row', null, 'flex', 'column')
            ->assertSet('properties', function ($properties) {
                return $properties['flex'] === 'column';
            });
    }

    /** @test */
    public function livewire_component_instantiation_works(): void
    {
        // Test direct component instantiation without Livewire testing framework
        $pageEditor = new PageEditor;
        $pageEditor->pageKey = 'test-page';
        $pageEditor->themeId = $this->theme->id;
        $pageEditor->page = $this->page;
        $pageEditor->rows = [];

        // Test addRow method directly
        $pageEditor->addRow();

        $this->assertCount(1, $pageEditor->rows);

        $rows = $pageEditor->rows;
        $lastRow = end($rows);

        $this->assertArrayHasKey('blocks', $lastRow);
        $this->assertArrayHasKey('properties', $lastRow);
        $this->assertEmpty($lastRow['blocks']);
        $this->assertIsArray($lastRow['properties']);
    }

    /** @test */
    public function row_block_direct_instantiation_works(): void
    {
        $rowBlock = new RowBlock;
        $rowBlock->rowId = 'test-row';
        $rowBlock->blocks = [];
        $rowBlock->properties = ['flex' => 'row'];

        // Test updateBlockProperty method directly
        $rowBlock->updateBlockProperty('test-row', null, 'flex', 'column');

        $this->assertEquals('column', $rowBlock->properties['flex']);
    }

    /** @test */
    public function page_editor_can_handle_row_operations_directly(): void
    {
        $pageEditor = new PageEditor;
        $pageEditor->pageKey = 'test-page';
        $pageEditor->themeId = $this->theme->id;
        $pageEditor->page = $this->page;
        $pageEditor->rows = [
            'row-1' => ['blocks' => [], 'properties' => []],
            'row-2' => ['blocks' => [], 'properties' => []],
        ];

        // Test move operations
        $pageEditor->moveRowUp('row-2');

        $rowIds = array_keys($pageEditor->rows);
        $this->assertEquals('row-2', $rowIds[0]);
        $this->assertEquals('row-1', $rowIds[1]);

        // Test delete operation
        $pageEditor->deleteRow('row-2');
        $this->assertCount(1, $pageEditor->rows);
        $this->assertArrayHasKey('row-1', $pageEditor->rows);
    }

    /** @test */
    public function row_block_can_handle_block_operations_directly(): void
    {
        $rowBlock = new RowBlock;
        $rowBlock->rowId = 'test-row';
        $rowBlock->blocks = [
            'block-1' => ['alias' => 'first-block'],
            'block-2' => ['alias' => 'second-block'],
        ];
        $rowBlock->properties = [];

        // Test move operations
        $rowBlock->moveBlockUp('block-2');

        $blockIds = array_keys($rowBlock->blocks);
        $this->assertEquals('block-2', $blockIds[0]);
        $this->assertEquals('block-1', $blockIds[1]);

        // Test delete operation
        $rowBlock->deleteBlock('block-1');
        $this->assertCount(1, $rowBlock->blocks);
        $this->assertArrayHasKey('block-2', $rowBlock->blocks);
    }

    /** @test */
    public function page_editor_preserves_data_integrity(): void
    {
        $pageEditor = new PageEditor;
        $pageEditor->pageKey = 'test-page';
        $pageEditor->themeId = $this->theme->id;
        $pageEditor->page = $this->page;
        $pageEditor->rows = [];

        // Add row
        $pageEditor->addRow();
        $rowId = array_keys($pageEditor->rows)[0];

        // Update row properties
        $pageEditor->updateBlockProperty($rowId, null, 'flex', 'column');

        $originalRows = $pageEditor->rows;

        // Save
        $pageEditor->savePage();

        // Verify the component's state hasn't changed after save
        $this->assertEquals($originalRows, $pageEditor->rows);

        // Verify database matches component state
        $this->page->refresh();
        $this->assertEquals($originalRows, $this->page->components);
    }

    /** @test */
    public function nested_row_sync_works_directly(): void
    {
        $pageEditor = new PageEditor;
        $pageEditor->pageKey = 'test-page';
        $pageEditor->themeId = $this->theme->id;
        $pageEditor->page = $this->page;
        $pageEditor->rows = [
            'row-1' => [
                'blocks' => [
                    'nested-row' => [
                        'alias' => 'row-block',
                        'properties' => [],
                        'blocks' => ['old-block' => ['alias' => 'old']],
                    ],
                ],
                'properties' => [],
            ],
        ];

        $newBlocks = [
            'new-block-1' => ['alias' => 'test-block-1'],
            'new-block-2' => ['alias' => 'test-block-2'],
        ];

        $pageEditor->syncNestedRowData('nested-row', $newBlocks);

        $nestedBlocks = $pageEditor->rows['row-1']['blocks']['nested-row']['blocks'];
        $this->assertEquals($newBlocks, $nestedBlocks);
    }
}
