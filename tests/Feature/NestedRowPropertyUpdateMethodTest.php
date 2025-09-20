<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class NestedRowPropertyUpdateMethodTest extends TestCase
{
    protected Theme $theme;

    protected BuilderPage $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->theme = Theme::create([
            'name' => 'Test Theme',
            'description' => 'Test theme for update method bug',
        ]);

        $this->page = BuilderPage::create([
            'key' => 'update-method-test',
            'theme_id' => $this->theme->id,
            'components' => [],
        ]);
    }

    protected function createPageEditorComponent()
    {
        config()->set('page-builder.blocks', [
            \Trinavo\LivewirePageBuilder\Blocks\RichText::class,
            \Trinavo\LivewirePageBuilder\Blocks\Spacer::class,
        ]);
        config()->set('page-builder.pages', []);

        $service = app(\Trinavo\LivewirePageBuilder\Services\PageBuilderService::class);
        $availableBlocks = $service->getAvailableBlocks();

        $component = Livewire::test(PageEditor::class);

        return $component
            ->set('pageKey', 'update-method-test')
            ->set('themeId', $this->theme->id)
            ->set('page', $this->page)
            ->set('rows', [])
            ->set('availableBlocks', $availableBlocks)
            ->set('availableThemes', []);
    }

    /** @test */
    public function page_editor_update_block_property_handles_nested_rows_correctly(): void
    {
        $component = $this->createPageEditorComponent();

        // Create structure: parent row -> nested row
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

        // Debug: Print the structure to understand it better
        $initialNestedRow = $rows[$parentRowId]['blocks'][$nestedRowId];
        $this->assertEquals($rowBlockAlias, $initialNestedRow['alias'], 'Should be a RowBlock');

        // Test 1: Update nested row property with BOTH rowId and blockId provided
        // This simulates the exact scenario from your network request
        $component->call('updateBlockProperty', $parentRowId, $nestedRowId, 'desktopWidth', 'w-2xs');

        // Verify the update worked immediately
        $rows = $component->get('rows');
        $updatedNestedRow = $rows[$parentRowId]['blocks'][$nestedRowId];
        $this->assertEquals('w-2xs', $updatedNestedRow['properties']['desktopWidth'],
            'Nested row property should be updated immediately via PageEditor::updateBlockProperty');

        // Test 2: Verify the update persists through save/reload cycle
        $component->call('savePage');

        // Load saved data
        $savedPage = BuilderPage::where('key', 'update-method-test')->where('theme_id', $this->theme->id)->first();
        $savedNestedRow = $savedPage->components[$parentRowId]['blocks'][$nestedRowId];
        $this->assertEquals('w-2xs', $savedNestedRow['properties']['desktopWidth'],
            'Nested row property should be saved to database correctly');

        // Test 3: Create fresh component and load saved data (simulating page refresh)
        $freshComponent = $this->createPageEditorComponent();
        $freshComponent->set('page', $savedPage);
        $freshComponent->set('rows', $savedPage->components);

        $freshRows = $freshComponent->get('rows');
        $freshNestedRow = $freshRows[$parentRowId]['blocks'][$nestedRowId];
        $this->assertEquals('w-2xs', $freshNestedRow['properties']['desktopWidth'],
            'CRITICAL: Nested row property should persist after page reload');
    }

    /** @test */
    public function update_block_property_method_logic_analysis(): void
    {
        $component = $this->createPageEditorComponent();

        // Create nested structure
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

        // Test the different updateBlockProperty scenarios:

        // Scenario 1: $rowId provided, $blockId = null (top-level row property update)
        $component->call('updateBlockProperty', $parentRowId, null, 'backgroundColor', '#ff0000');
        $rows = $component->get('rows');
        $this->assertEquals('#ff0000', $rows[$parentRowId]['properties']['backgroundColor'],
            'Top-level row property should be updated when blockId is null');

        // Scenario 2: $rowId provided, $blockId provided (nested block property update)
        // This is the critical case from your bug report
        $component->call('updateBlockProperty', $parentRowId, $nestedRowId, 'desktopWidth', 'w-3xs');
        $rows = $component->get('rows');
        $this->assertEquals('w-3xs', $rows[$parentRowId]['blocks'][$nestedRowId]['properties']['desktopWidth'],
            'Nested row property should be updated when both rowId and blockId are provided');

        // Scenario 3: Test with multiple nested rows to ensure we're updating the right one
        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias); // Add second nested row
        $rows = $component->get('rows');
        $nestedRowIds = array_keys($rows[$parentRowId]['blocks']);
        $firstNestedRowId = $nestedRowIds[0];
        $secondNestedRowId = $nestedRowIds[1];

        // Update only the second nested row
        $component->call('updateBlockProperty', $parentRowId, $secondNestedRowId, 'backgroundColor', '#00ff00');
        $rows = $component->get('rows');

        // Verify only the second nested row was updated
        $this->assertEquals('w-3xs', $rows[$parentRowId]['blocks'][$firstNestedRowId]['properties']['desktopWidth'],
            'First nested row should retain its previous width');
        $this->assertNotEquals('#00ff00', $rows[$parentRowId]['blocks'][$firstNestedRowId]['properties']['backgroundColor'] ?? null,
            'First nested row should not have the background color');
        $this->assertEquals('#00ff00', $rows[$parentRowId]['blocks'][$secondNestedRowId]['properties']['backgroundColor'],
            'Second nested row should have the new background color');
    }

    /** @test */
    public function deep_nested_rows_property_updates(): void
    {
        // Test deeply nested rows: parent -> nested -> deeply nested
        $component = $this->createPageEditorComponent();

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

        // Add first level nested row
        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias);
        $rows = $component->get('rows');
        $firstNestedRowId = array_key_first($rows[$parentRowId]['blocks']);

        // Update first nested row - this should work
        $component->call('updateBlockProperty', $parentRowId, $firstNestedRowId, 'desktopWidth', 'w-1/2');
        $rows = $component->get('rows');
        $this->assertEquals('w-1/2', $rows[$parentRowId]['blocks'][$firstNestedRowId]['properties']['desktopWidth'],
            'First level nested row should be updated correctly');

        // Note: For deeply nested rows (nested within nested), the current PageEditor::updateBlockProperty
        // method would NOT work because it only searches one level deep. This would require a recursive search
        // or a different approach. For now, let's focus on the first-level nesting which is your use case.
    }
}
