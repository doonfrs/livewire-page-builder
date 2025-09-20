<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class PropertyPanelIDConfusionTest extends TestCase
{
    protected Theme $theme;
    protected BuilderPage $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->theme = Theme::create([
            'name' => 'Test Theme',
            'description' => 'Test theme for property panel ID confusion',
        ]);

        $this->page = BuilderPage::create([
            'key' => 'id-confusion-test',
            'theme_id' => $this->theme->id,
            'components' => [],
        ]);
    }

    protected function createPageEditorComponent()
    {
        config()->set('page-builder.blocks', [
            \Trinavo\LivewirePageBuilder\Blocks\RichText::class,
            \Trinavo\LivewirePageBuilder\Blocks\Section::class,
        ]);
        config()->set('page-builder.pages', []);

        $service = app(\Trinavo\LivewirePageBuilder\Services\PageBuilderService::class);
        $availableBlocks = $service->getAvailableBlocks();

        $component = Livewire::test(PageEditor::class);
        return $component
            ->set('pageKey', 'id-confusion-test')
            ->set('themeId', $this->theme->id)
            ->set('page', $this->page)
            ->set('rows', [])
            ->set('availableBlocks', $availableBlocks)
            ->set('availableThemes', []);
    }

    /** @test */
    public function test_property_panel_id_passing_for_nested_rows(): void
    {
        // This test reproduces the potential ID confusion from your network request
        // where rowId might be the nested row's own ID instead of the parent row ID

        $component = $this->createPageEditorComponent();

        // Create parent row with nested row
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
        $rows = $component->get('rows');
        $nestedRowId = array_key_first($rows[$parentRowId]['blocks']);

        // Test the CORRECT way: parentRowId as rowId, nestedRowId as blockId
        $component->call('updateBlockProperty', $parentRowId, $nestedRowId, 'desktopWidth', 'w-correct');
        $rows = $component->get('rows');
        $this->assertEquals('w-correct', $rows[$parentRowId]['blocks'][$nestedRowId]['properties']['desktopWidth'],
            'Correct ID usage should work: parentRowId as rowId, nestedRowId as blockId');

        // Test what used to be the INCORRECT way but is now FIXED: nestedRowId as rowId, null as blockId
        // This simulates the property panel using the nested row ID directly
        $component->call('updateBlockProperty', $nestedRowId, null, 'desktopWidth', 'w-fixed');
        $rows = $component->get('rows');

        // This update should NOW work because we fixed the nested row property bug
        $this->assertEquals('w-fixed', $rows[$parentRowId]['blocks'][$nestedRowId]['properties']['desktopWidth'],
            'FIXED: Nested row property updates now work when using nestedRowId as rowId with null blockId');

        // Test another incorrect way: nestedRowId as both rowId and blockId
        $component->call('updateBlockProperty', $nestedRowId, $nestedRowId, 'desktopWidth', 'w-double-incorrect');
        $rows = $component->get('rows');

        // This should also not work, so the property should remain as it was after the previous fix
        $this->assertEquals('w-fixed', $rows[$parentRowId]['blocks'][$nestedRowId]['properties']['desktopWidth'],
            'Double incorrect ID usage should NOT update: the property should remain w-fixed from previous update');
    }

    /** @test */
    public function test_component_interaction_simulation(): void
    {
        // This test simulates the component interaction flow that might be causing the issue

        $component = $this->createPageEditorComponent();

        // Create structure
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
        $rows = $component->get('rows');
        $nestedRowId = array_key_first($rows[$parentRowId]['blocks']);

        // Simulate the property panel being opened for the nested row
        // In the real app, when you select a nested row, the property panel should show that nested row's properties

        // When the property panel opens for a nested row (RowBlock), it needs to know:
        // 1. Which parent row contains this nested row (parentRowId)
        // 2. Which nested row we're editing (nestedRowId)

        // The CORRECT call should be:
        $component->call('updateBlockProperty', $parentRowId, $nestedRowId, 'desktopWidth', 'w-2xs');

        // Verify this worked
        $rows = $component->get('rows');
        $this->assertEquals('w-2xs', $rows[$parentRowId]['blocks'][$nestedRowId]['properties']['desktopWidth']);

        // Save and reload to test persistence through the complete cycle
        $component->call('savePage');
        $savedPage = BuilderPage::where('key', 'id-confusion-test')->where('theme_id', $this->theme->id)->first();

        // Test reload
        $freshComponent = $this->createPageEditorComponent();
        $freshComponent->set('page', $savedPage);
        $freshComponent->set('rows', $savedPage->components);

        $freshRows = $freshComponent->get('rows');
        $this->assertEquals('w-2xs', $freshRows[$parentRowId]['blocks'][$nestedRowId]['properties']['desktopWidth'],
            'Property should persist after save/reload cycle');

        // Additional test: Verify that the RowBlock component itself maintains the property
        $nestedRowData = $freshRows[$parentRowId]['blocks'][$nestedRowId];

        // Simulate how the nested RowBlock component is instantiated in the real app
        $rowBlockComponent = new \Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock();
        $rowBlockComponent->rowId = $nestedRowId;
        $rowBlockComponent->properties = $nestedRowData['properties'];
        $rowBlockComponent->blocks = $nestedRowData['blocks'] ?? [];
        $rowBlockComponent->editMode = true;

        $rowBlockComponent->mount();

        $this->assertEquals('w-2xs', $rowBlockComponent->properties['desktopWidth'],
            'RowBlock component should preserve the desktopWidth property after mount');
    }

    /** @test */
    public function simulate_browser_network_request_exactly(): void
    {
        // This test simulates the exact network request you showed me
        // "value":"w-3xs","rowId":"68cecac646b82","blockId":null

        $component = $this->createPageEditorComponent();

        // Create structure with specific IDs to match your scenario
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
        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias); // Add second nested row as per your description

        $rows = $component->get('rows');
        $nestedRowIds = array_keys($rows[$parentRowId]['blocks']);
        $targetNestedRowId = $nestedRowIds[0]; // This represents "68cecac646b82"

        // Your network request shows: rowId:"68cecac646b82", blockId:null
        // This suggests the FlexibleSizeProperty component is treating the NESTED row ID as the rowId
        // and blockId as null, which would trigger the first branch of updateBlockProperty method

        // Test this scenario:
        $component->call('updateBlockProperty', $targetNestedRowId, null, 'desktopWidth', 'w-2xs');

        // This should NOW work because we fixed the nested row property bug
        $rows = $component->get('rows');
        $nestedRow = $rows[$parentRowId]['blocks'][$targetNestedRowId];

        // The property should be updated successfully
        $actualWidth = $nestedRow['properties']['desktopWidth'] ?? 'w-full';

        // BUG IS NOW FIXED: When rowId is the nested row ID and blockId is null, the update should work
        $this->assertEquals('w-2xs', $actualWidth,
            'BUG FIXED: When rowId is the nested row ID and blockId is null, the update now works correctly');

        // Now test the CORRECT way:
        $component->call('updateBlockProperty', $parentRowId, $targetNestedRowId, 'desktopWidth', 'w-2xs');
        $rows = $component->get('rows');
        $this->assertEquals('w-2xs', $rows[$parentRowId]['blocks'][$targetNestedRowId]['properties']['desktopWidth'],
            'CORRECT: When rowId is parent and blockId is nested row, update should work');
    }
}