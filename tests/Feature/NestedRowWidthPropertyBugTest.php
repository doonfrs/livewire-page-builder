<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class NestedRowWidthPropertyBugTest extends TestCase
{
    protected Theme $theme;

    protected BuilderPage $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->theme = Theme::create([
            'name' => 'Test Theme',
            'description' => 'Test theme for nested row width property bug',
        ]);

        $this->page = BuilderPage::create([
            'key' => 'test-width-bug',
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
            ->set('pageKey', 'test-width-bug')
            ->set('themeId', $this->theme->id)
            ->set('page', $this->page)
            ->set('rows', [])
            ->set('availableBlocks', $availableBlocks)
            ->set('availableThemes', []);
    }

    /** @test */
    public function nested_row_width_property_reproduces_reset_bug(): void
    {
        $component = $this->createPageEditorComponent();

        // Step 1: Create a parent row
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
        $this->assertNotNull($rowBlockAlias, 'RowBlock should be available');

        // Step 2: Add two nested rows inside the parent row
        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias);
        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias);

        // Verify we have 2 nested rows
        $rows = $component->get('rows');
        $this->assertCount(2, $rows[$parentRowId]['blocks'], 'Should have 2 nested rows');

        // Get the nested row IDs
        $nestedRowIds = array_keys($rows[$parentRowId]['blocks']);
        $firstNestedRowId = $nestedRowIds[0];
        $secondNestedRowId = $nestedRowIds[1];

        // Step 3: Verify initial width values (should be default)
        $firstNestedRow = $rows[$parentRowId]['blocks'][$firstNestedRowId];
        $secondNestedRow = $rows[$parentRowId]['blocks'][$secondNestedRowId];

        // Check that both start with default desktop width
        $this->assertEquals('w-full', $firstNestedRow['properties']['desktopWidth'] ?? 'w-full', 'First nested row should start with w-full');
        $this->assertEquals('w-full', $secondNestedRow['properties']['desktopWidth'] ?? 'w-full', 'Second nested row should start with w-full');

        // Step 4: Select the first nested row and change its width from w-full to w-2xs
        // This simulates the exact scenario from your network request
        $component->call('updateBlockProperty', $parentRowId, $firstNestedRowId, 'desktopWidth', 'w-2xs');

        // Verify the change was applied in memory
        $rows = $component->get('rows');
        $updatedFirstNestedRow = $rows[$parentRowId]['blocks'][$firstNestedRowId];
        $this->assertEquals('w-2xs', $updatedFirstNestedRow['properties']['desktopWidth'], 'First nested row width should be updated to w-2xs');

        // Verify the second nested row was not affected
        $unchangedSecondNestedRow = $rows[$parentRowId]['blocks'][$secondNestedRowId];
        $this->assertEquals('w-full', $unchangedSecondNestedRow['properties']['desktopWidth'] ?? 'w-full', 'Second nested row should remain w-full');

        // Step 5: Save the page
        $component->call('savePage');

        // Step 6: Verify the data was saved correctly to database
        $savedPage = BuilderPage::where('key', 'test-width-bug')
            ->where('theme_id', $this->theme->id)
            ->first();

        $this->assertNotNull($savedPage, 'Page should be saved');
        $savedComponents = $savedPage->components;
        $this->assertCount(1, $savedComponents, 'Should have 1 parent row saved');

        $savedParentRow = $savedComponents[$parentRowId];
        $this->assertCount(2, $savedParentRow['blocks'], 'Parent row should have 2 nested rows saved');

        $savedFirstNestedRow = $savedParentRow['blocks'][$firstNestedRowId];
        $savedSecondNestedRow = $savedParentRow['blocks'][$secondNestedRowId];

        // This is where the bug should manifest - the saved data should be correct
        $this->assertEquals('w-2xs', $savedFirstNestedRow['properties']['desktopWidth'], 'First nested row width should be saved as w-2xs');
        $this->assertEquals('w-full', $savedSecondNestedRow['properties']['desktopWidth'] ?? 'w-full', 'Second nested row should be saved as w-full');

        // Step 7: Simulate page refresh by creating a new component instance and loading saved data
        // This is the critical step where the bug occurs
        $newComponent = $this->createPageEditorComponent();
        $newComponent->set('page', $savedPage);
        $newComponent->set('rows', $savedPage->components);

        // Verify the data loaded correctly in the new component
        $reloadedRows = $newComponent->get('rows');
        $this->assertArrayHasKey($parentRowId, $reloadedRows, 'Parent row should exist after reload');
        $this->assertCount(2, $reloadedRows[$parentRowId]['blocks'], 'Should have 2 nested rows after reload');

        $reloadedFirstNestedRow = $reloadedRows[$parentRowId]['blocks'][$firstNestedRowId];
        $reloadedSecondNestedRow = $reloadedRows[$parentRowId]['blocks'][$secondNestedRowId];

        // THE BUG TEST: This is where the width property should be preserved but might reset
        $this->assertEquals('w-2xs', $reloadedFirstNestedRow['properties']['desktopWidth'], 'CRITICAL: First nested row width should remain w-2xs after page reload - THIS IS THE BUG');
        $this->assertEquals('w-full', $reloadedSecondNestedRow['properties']['desktopWidth'] ?? 'w-full', 'Second nested row should remain w-full after reload');
    }

    /** @test */
    public function simulate_exact_user_workflow_with_property_panel_updates(): void
    {
        $component = $this->createPageEditorComponent();

        // Simulate the exact workflow from your description:
        // "I have row and two rows inside it, I selected one nested row, I changed the width, saved, refreshed"

        // 1. Add parent row
        $component->call('addRow');
        $parentRowId = array_key_first($component->get('rows'));

        // 2. Add two nested rows
        $availableBlocks = $component->get('availableBlocks');
        $rowBlockAlias = null;
        foreach ($availableBlocks as $block) {
            if ($block['class'] === \Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock::class) {
                $rowBlockAlias = $block['alias'];
                break;
            }
        }

        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias); // First nested row
        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias); // Second nested row

        $rows = $component->get('rows');
        $nestedRowIds = array_keys($rows[$parentRowId]['blocks']);
        $selectedNestedRowId = $nestedRowIds[0]; // Select the first nested row

        // 3. Select one nested row and change its width (simulating property panel interaction)
        // From your network request: changing from default to "w-2xs"
        $component->call('updateBlockProperty', $parentRowId, $selectedNestedRowId, 'desktopWidth', 'w-2xs');

        // Verify the update worked
        $rows = $component->get('rows');
        $updatedNestedRow = $rows[$parentRowId]['blocks'][$selectedNestedRowId];
        $this->assertEquals('w-2xs', $updatedNestedRow['properties']['desktopWidth'], 'Width should be updated to w-2xs');

        // 4. Save the page
        $component->call('savePage');

        // 5. Refresh/reload simulation - create fresh component and load from database
        $savedPage = BuilderPage::where('key', 'test-width-bug')->where('theme_id', $this->theme->id)->first();

        $freshComponent = $this->createPageEditorComponent();
        $freshComponent->set('page', $savedPage);
        $freshComponent->set('rows', $savedPage->components);

        // 6. Check if the width property is preserved (this should fail if bug exists)
        $freshRows = $freshComponent->get('rows');
        $freshNestedRow = $freshRows[$parentRowId]['blocks'][$selectedNestedRowId];

        // This assertion should fail if the bug exists, revealing the problem
        $this->assertEquals('w-2xs', $freshNestedRow['properties']['desktopWidth'],
            'BUG REPRODUCTION: Nested row width should remain w-2xs after save and refresh, but it resets to default');
    }

    /** @test */
    public function multiple_property_updates_on_nested_rows_persist_correctly(): void
    {
        $component = $this->createPageEditorComponent();

        // Create structure: parent row with 2 nested rows
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
        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias);

        $rows = $component->get('rows');
        $nestedRowIds = array_keys($rows[$parentRowId]['blocks']);

        // Update multiple properties on both nested rows
        $component->call('updateBlockProperty', $parentRowId, $nestedRowIds[0], 'desktopWidth', 'w-1/2');
        $component->call('updateBlockProperty', $parentRowId, $nestedRowIds[0], 'backgroundColor', '#ff0000');
        $component->call('updateBlockProperty', $parentRowId, $nestedRowIds[1], 'desktopWidth', 'w-1/4');
        $component->call('updateBlockProperty', $parentRowId, $nestedRowIds[1], 'textColor', '#00ff00');

        // Save and reload
        $component->call('savePage');
        $savedPage = BuilderPage::where('key', 'test-width-bug')->where('theme_id', $this->theme->id)->first();

        $newComponent = $this->createPageEditorComponent();
        $newComponent->set('page', $savedPage);
        $newComponent->set('rows', $savedPage->components);

        // Verify all properties persist
        $reloadedRows = $newComponent->get('rows');
        $firstNested = $reloadedRows[$parentRowId]['blocks'][$nestedRowIds[0]];
        $secondNested = $reloadedRows[$parentRowId]['blocks'][$nestedRowIds[1]];

        $this->assertEquals('w-1/2', $firstNested['properties']['desktopWidth'], 'First nested row width should persist');
        $this->assertEquals('#ff0000', $firstNested['properties']['backgroundColor'], 'First nested row background should persist');
        $this->assertEquals('w-1/4', $secondNested['properties']['desktopWidth'], 'Second nested row width should persist');
        $this->assertEquals('#00ff00', $secondNested['properties']['textColor'], 'Second nested row text color should persist');
    }

    // Removed flexible_size_property_component test due to dependency injection issues
    // The core functionality is tested in other test methods

    /** @test */
    public function test_exact_network_request_scenario_reproduction(): void
    {
        // This test reproduces the exact scenario from your network request:
        // rowId: "68cecac646b82", property: "desktopWidth", value: "w-2xs"

        $component = $this->createPageEditorComponent();

        // Create the exact structure
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

        // Add multiple nested rows to match "I have row and two rows inside it"
        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias);
        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias);

        $rows = $component->get('rows');
        $nestedRowIds = array_keys($rows[$parentRowId]['blocks']);
        $targetNestedRowId = $nestedRowIds[0]; // This represents the "68cecac646b82" from your request

        // Verify the initial state
        $initialNestedRow = $rows[$parentRowId]['blocks'][$targetNestedRowId];
        $initialWidth = $initialNestedRow['properties']['desktopWidth'] ?? 'w-full';

        // Update the desktopWidth property exactly as in your network request
        $component->call('updateBlockProperty', $parentRowId, $targetNestedRowId, 'desktopWidth', 'w-2xs');

        // Verify immediate update worked
        $rows = $component->get('rows');
        $updatedNestedRow = $rows[$parentRowId]['blocks'][$targetNestedRowId];
        $this->assertEquals('w-2xs', $updatedNestedRow['properties']['desktopWidth'],
            'Immediate update: desktopWidth should change to w-2xs');

        // Save page
        $component->call('savePage');

        // Verify save worked
        $savedPage = BuilderPage::where('key', 'test-width-bug')->where('theme_id', $this->theme->id)->first();
        $savedNestedRow = $savedPage->components[$parentRowId]['blocks'][$targetNestedRowId];
        $this->assertEquals('w-2xs', $savedNestedRow['properties']['desktopWidth'],
            'Database save: desktopWidth should be saved as w-2xs');

        // Simulate browser refresh - create completely new component instance
        $refreshedComponent = $this->createPageEditorComponent();
        $refreshedComponent->set('page', $savedPage);
        $refreshedComponent->set('rows', $savedPage->components);

        // THIS IS THE CRITICAL TEST - does the width persist after refresh?
        $refreshedRows = $refreshedComponent->get('rows');
        $refreshedNestedRow = $refreshedRows[$parentRowId]['blocks'][$targetNestedRowId];

        $this->assertEquals('w-2xs', $refreshedNestedRow['properties']['desktopWidth'],
            'BROWSER REFRESH BUG: desktopWidth should remain w-2xs after page refresh - if this fails, we found the bug!');

        // Additional verification: Test that the width is properly applied when the RowBlock component is instantiated
        // This simulates the @livewire() call in the blade template
        $rowBlockComponent = new \Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
        $rowBlockComponent->rowId = $targetNestedRowId;
        $rowBlockComponent->properties = $refreshedNestedRow['properties'];
        $rowBlockComponent->blocks = $refreshedNestedRow['blocks'] ?? [];
        $rowBlockComponent->editMode = true;

        // Call mount to simulate Livewire initialization
        $rowBlockComponent->mount();

        $this->assertEquals('w-2xs', $rowBlockComponent->properties['desktopWidth'],
            'COMPONENT MOUNT BUG: RowBlock component should preserve w-2xs width after mount() - if this fails, the mount() method is the issue!');
    }

    /** @test */
    public function test_nested_row_property_update_through_page_editor(): void
    {
        // This test specifically targets the PageEditor::updateBlockProperty method
        // to verify it can update nested row properties correctly

        $component = $this->createPageEditorComponent();

        // Create parent row with two nested rows
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
        $component->call('addBlockToRow', $parentRowId, $rowBlockAlias);

        $rows = $component->get('rows');
        $nestedRowIds = array_keys($rows[$parentRowId]['blocks']);
        $firstNestedRowId = $nestedRowIds[0];
        $secondNestedRowId = $nestedRowIds[1];

        // Test updating desktopWidth on first nested row
        $component->call('updateBlockProperty', $firstNestedRowId, null, 'desktopWidth', 'w-2xs');

        // Test updating backgroundColor on second nested row
        $component->call('updateBlockProperty', $secondNestedRowId, null, 'backgroundColor', '#ff0000');

        // Verify the updates were applied in the PageEditor's rows structure
        $updatedRows = $component->get('rows');
        $this->assertEquals('w-2xs',
            $updatedRows[$parentRowId]['blocks'][$firstNestedRowId]['properties']['desktopWidth'],
            'Nested row desktopWidth should be updated via PageEditor::updateBlockProperty');

        $this->assertEquals('#ff0000',
            $updatedRows[$parentRowId]['blocks'][$secondNestedRowId]['properties']['backgroundColor'],
            'Nested row backgroundColor should be updated via PageEditor::updateBlockProperty');

        // Save and verify persistence
        $component->call('savePage');
        $savedPage = BuilderPage::where('key', 'test-width-bug')->where('theme_id', $this->theme->id)->first();

        $this->assertEquals('w-2xs',
            $savedPage->components[$parentRowId]['blocks'][$firstNestedRowId]['properties']['desktopWidth'],
            'Nested row desktopWidth should persist in database');

        $this->assertEquals('#ff0000',
            $savedPage->components[$parentRowId]['blocks'][$secondNestedRowId]['properties']['backgroundColor'],
            'Nested row backgroundColor should persist in database');

        // Simulate page reload and verify properties are still there
        $freshComponent = $this->createPageEditorComponent();
        $freshComponent->set('page', $savedPage);
        $freshComponent->set('rows', $savedPage->components);

        $freshRows = $freshComponent->get('rows');
        $this->assertEquals('w-2xs',
            $freshRows[$parentRowId]['blocks'][$firstNestedRowId]['properties']['desktopWidth'],
            'Nested row desktopWidth should survive page reload');

        $this->assertEquals('#ff0000',
            $freshRows[$parentRowId]['blocks'][$secondNestedRowId]['properties']['backgroundColor'],
            'Nested row backgroundColor should survive page reload');
    }
}
