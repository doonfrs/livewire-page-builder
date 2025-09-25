<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class NestedRowPropertyPersistenceTest extends TestCase
{
    protected Theme $theme;

    protected BuilderPage $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->theme = Theme::create([
            'name' => 'Test Theme',
            'description' => 'Test theme for nested row property persistence',
        ]);

        $this->page = BuilderPage::create([
            'key' => 'test-page-persistence',
            'theme_id' => $this->theme->id,
            'components' => [],
        ]);
    }

    protected function createPageEditorComponent()
    {
        // Mock the config for the service to work properly in package testing
        config()->set('page-builder.blocks', [
            \Trinavo\LivewirePageBuilder\Blocks\RichText::class,
            \Trinavo\LivewirePageBuilder\Blocks\Spacer::class,
        ]);
        config()->set('page-builder.pages', []);

        // Use the actual service to get available blocks (including RowBlock)
        $service = app(\Trinavo\LivewirePageBuilder\Services\PageBuilderService::class);
        $availableBlocks = $service->getAvailableBlocks();

        $component = Livewire::test(PageEditor::class);

        return $component
            ->set('pageKey', 'test-page-persistence')
            ->set('themeId', $this->theme->id)
            ->set('page', $this->page)
            ->set('rows', [])
            ->set('availableBlocks', $availableBlocks)
            ->set('availableThemes', []);
    }

    /** @test */
    public function nested_row_background_color_persists_after_save_and_reload(): void
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

        // Get the nested row block ID
        $rows = $component->get('rows');
        $nestedRowBlockId = array_key_first($rows[$parentRowId]['blocks']);
        $nestedRowBlock = $rows[$parentRowId]['blocks'][$nestedRowBlockId];

        // Verify nested row was created properly
        $this->assertEquals($rowBlockAlias, $nestedRowBlock['alias'], 'Nested block should be a RowBlock');
        $this->assertArrayHasKey('blocks', $nestedRowBlock, 'Nested row should have blocks array');

        // Change the nested row background color
        $testBackgroundColor = '#ff0000'; // Red color
        $component->call('updateBlockProperty', $parentRowId, $nestedRowBlockId, 'backgroundColor', $testBackgroundColor);

        // Verify the property was updated in memory
        $rows = $component->get('rows');
        $updatedNestedRowBlock = $rows[$parentRowId]['blocks'][$nestedRowBlockId];
        $this->assertEquals(
            $testBackgroundColor,
            $updatedNestedRowBlock['properties']['backgroundColor'],
            'Background color should be updated in memory'
        );

        // Save the page
        $component->call('savePage');

        // Reload the page from database to verify persistence
        $savedPage = BuilderPage::where('key', 'test-page-persistence')
            ->where('theme_id', $this->theme->id)
            ->first();

        $this->assertNotNull($savedPage, 'Page should be saved');
        $savedComponents = $savedPage->components;
        $this->assertCount(1, $savedComponents, 'Should save 1 parent row');

        // Verify the parent row structure
        $savedParentRow = $savedComponents[$parentRowId];
        $this->assertCount(1, $savedParentRow['blocks'], 'Parent row should have 1 nested row');

        // Find and verify the nested row block
        $savedNestedRowBlock = $savedParentRow['blocks'][$nestedRowBlockId];
        $this->assertEquals($rowBlockAlias, $savedNestedRowBlock['alias'], 'Saved nested row should be a RowBlock');
        $this->assertArrayHasKey('blocks', $savedNestedRowBlock, 'Saved nested row should have blocks array');
        $this->assertArrayHasKey('properties', $savedNestedRowBlock, 'Saved nested row should have properties');

        // Verify the background color was persisted
        $this->assertEquals(
            $testBackgroundColor,
            $savedNestedRowBlock['properties']['backgroundColor'],
            'Background color should persist after save and reload'
        );
    }

    /** @test */
    public function multiple_nested_row_properties_persist_correctly(): void
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

        // Add two nested rows
        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias);
        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias);

        // Get nested row block IDs
        $rows = $component->get('rows');
        $nestedRowBlockIds = array_keys($rows[$parentRowId]['blocks']);
        $firstNestedRowId = $nestedRowBlockIds[0];
        $secondNestedRowId = $nestedRowBlockIds[1];

        // Set different properties for each nested row
        $firstRowBgColor = '#ff0000'; // Red
        $secondRowBgColor = '#00ff00'; // Green
        $firstRowTextColor = '#ffffff'; // White
        $secondRowTextColor = '#000000'; // Black
        $firstRowPadding = '20';
        $secondRowPadding = '40';

        // Update first nested row properties
        $component->call('updateBlockProperty', $parentRowId, $firstNestedRowId, 'backgroundColor', $firstRowBgColor);
        $component->call('updateBlockProperty', $parentRowId, $firstNestedRowId, 'textColor', $firstRowTextColor);
        $component->call('updateBlockProperty', $parentRowId, $firstNestedRowId, 'mobilePaddingTop', $firstRowPadding);

        // Update second nested row properties
        $component->call('updateBlockProperty', $parentRowId, $secondNestedRowId, 'backgroundColor', $secondRowBgColor);
        $component->call('updateBlockProperty', $parentRowId, $secondNestedRowId, 'textColor', $secondRowTextColor);
        $component->call('updateBlockProperty', $parentRowId, $secondNestedRowId, 'mobilePaddingTop', $secondRowPadding);

        // Save the page
        $component->call('savePage');

        // Reload and verify all properties persisted
        $savedPage = BuilderPage::where('key', 'test-page-persistence')
            ->where('theme_id', $this->theme->id)
            ->first();

        $savedComponents = $savedPage->components;
        $savedParentRow = $savedComponents[$parentRowId];
        $this->assertCount(2, $savedParentRow['blocks'], 'Should have 2 nested rows');

        // Verify first nested row properties
        $savedFirstNestedRow = $savedParentRow['blocks'][$firstNestedRowId];
        $this->assertEquals($firstRowBgColor, $savedFirstNestedRow['properties']['backgroundColor']);
        $this->assertEquals($firstRowTextColor, $savedFirstNestedRow['properties']['textColor']);
        $this->assertEquals($firstRowPadding, $savedFirstNestedRow['properties']['mobilePaddingTop']);

        // Verify second nested row properties
        $savedSecondNestedRow = $savedParentRow['blocks'][$secondNestedRowId];
        $this->assertEquals($secondRowBgColor, $savedSecondNestedRow['properties']['backgroundColor']);
        $this->assertEquals($secondRowTextColor, $savedSecondNestedRow['properties']['textColor']);
        $this->assertEquals($secondRowPadding, $savedSecondNestedRow['properties']['mobilePaddingTop']);
    }

    /** @test */
    public function nested_row_properties_reload_correctly_in_new_component_instance(): void
    {
        // First, create a page with nested rows and properties
        $component = $this->createPageEditorComponent();

        // Add parent row with nested row
        $component->call('addRow');
        $parentRowId = array_key_first($component->get('rows'));

        $availableBlocks = $component->get('availableBlocks');
        $rowBlockAlias = null;
        foreach ($availableBlocks as $block) {
            if ($block['class'] === \Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock::class) {
                $rowBlockAlias = $block['alias'];
                break;
            }
        }

        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias);

        // Set properties on nested row
        $rows = $component->get('rows');
        $nestedRowBlockId = array_key_first($rows[$parentRowId]['blocks']);
        $testBgColor = '#0000ff'; // Blue
        $testPadding = '30';

        $component->call('updateBlockProperty', $parentRowId, $nestedRowBlockId, 'backgroundColor', $testBgColor);
        $component->call('updateBlockProperty', $parentRowId, $nestedRowBlockId, 'mobilePaddingTop', $testPadding);

        // Save the page
        $component->call('savePage');

        // Now simulate page reload by creating a new component instance with existing page data
        $savedPage = BuilderPage::where('key', 'test-page-persistence')
            ->where('theme_id', $this->theme->id)
            ->first();

        $newComponent = $this->createPageEditorComponent();
        $newComponent->set('page', $savedPage);
        $newComponent->set('rows', $savedPage->components);

        // Verify the properties are loaded correctly in the new component
        $reloadedRows = $newComponent->get('rows');
        $this->assertArrayHasKey($parentRowId, $reloadedRows, 'Parent row should exist after reload');
        $this->assertArrayHasKey($nestedRowBlockId, $reloadedRows[$parentRowId]['blocks'], 'Nested row should exist after reload');

        $reloadedNestedRow = $reloadedRows[$parentRowId]['blocks'][$nestedRowBlockId];
        $this->assertEquals($testBgColor, $reloadedNestedRow['properties']['backgroundColor'], 'Background color should reload correctly');
        $this->assertEquals($testPadding, $reloadedNestedRow['properties']['mobilePaddingTop'], 'Padding should reload correctly');

        // Test that we can still update properties after reload
        $newBgColor = '#ffff00'; // Yellow
        $newComponent->call('updateBlockProperty', $parentRowId, $nestedRowBlockId, 'backgroundColor', $newBgColor);

        $updatedRows = $newComponent->get('rows');
        $updatedNestedRow = $updatedRows[$parentRowId]['blocks'][$nestedRowBlockId];
        $this->assertEquals($newBgColor, $updatedNestedRow['properties']['backgroundColor'], 'Should be able to update properties after reload');
    }

    /** @test */
    public function nested_row_property_changes_sync_correctly_with_parent(): void
    {
        $component = $this->createPageEditorComponent();

        // Add parent row with nested row
        $component->call('addRow');
        $parentRowId = array_key_first($component->get('rows'));

        $availableBlocks = $component->get('availableBlocks');
        $rowBlockAlias = null;
        foreach ($availableBlocks as $block) {
            if ($block['class'] === \Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock::class) {
                $rowBlockAlias = $block['alias'];
                break;
            }
        }

        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias);

        // Get initial structure
        $rows = $component->get('rows');
        $nestedRowBlockId = array_key_first($rows[$parentRowId]['blocks']);

        // Update nested row property multiple times
        $colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00'];
        foreach ($colors as $color) {
            $component->call('updateBlockProperty', $parentRowId, $nestedRowBlockId, 'backgroundColor', $color);

            // Verify the change is reflected immediately
            $updatedRows = $component->get('rows');
            $updatedNestedRow = $updatedRows[$parentRowId]['blocks'][$nestedRowBlockId];
            $this->assertEquals($color, $updatedNestedRow['properties']['backgroundColor'], "Color should update to {$color}");
        }

        // Save and verify final state
        $component->call('savePage');

        $savedPage = BuilderPage::where('key', 'test-page-persistence')
            ->where('theme_id', $this->theme->id)
            ->first();

        $savedNestedRow = $savedPage->components[$parentRowId]['blocks'][$nestedRowBlockId];
        $this->assertEquals(
            end($colors), // Last color in array
            $savedNestedRow['properties']['backgroundColor'],
            'Final background color should be persisted'
        );
    }

    /** @test */
    public function nested_row_block_component_initializes_properties_correctly(): void
    {
        // This test simulates the exact scenario when a nested RowBlock component
        // is instantiated via @livewire() with saved properties

        $savedProperties = [
            'backgroundColor' => '#ff0000',
            'textColor' => '#ffffff',
            'mobilePaddingTop' => '20',
            'flex' => 'column',
        ];

        $savedBlocks = [
            'test-block-id' => [
                'alias' => 'some-block-alias',
                'properties' => ['text' => 'Sample text'],
            ],
        ];

        // Create a RowBlock component directly with saved data (simulating @livewire instantiation)
        $rowBlock = new \Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
        $rowBlock->rowId = 'nested-row-test';
        $rowBlock->properties = $savedProperties;
        $rowBlock->blocks = $savedBlocks;
        $rowBlock->editMode = true;

        // Call mount to simulate Livewire initialization
        $rowBlock->mount();

        // Verify that saved properties are preserved and merged with defaults
        $this->assertEquals('#ff0000', $rowBlock->properties['backgroundColor'], 'Background color should be preserved from saved data');
        $this->assertEquals('#ffffff', $rowBlock->properties['textColor'], 'Text color should be preserved from saved data');
        $this->assertEquals('20', $rowBlock->properties['mobilePaddingTop'], 'Padding should be preserved from saved data');
        $this->assertEquals('column', $rowBlock->properties['flex'], 'Flex direction should be preserved from saved data');

        // Verify that default properties are still present for unspecified values
        // Check properties that should exist from the parent Block class
        $this->assertArrayHasKey('selfCentered', $rowBlock->properties, 'Default selfCentered should be present');

        // Verify blocks are preserved
        $this->assertEquals($savedBlocks, $rowBlock->blocks, 'Nested blocks should be preserved');
    }
}
