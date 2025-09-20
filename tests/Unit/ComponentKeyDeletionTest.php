<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class ComponentKeyDeletionTest extends TestCase
{
    /** @test */
    public function component_key_changes_when_nested_row_is_deleted(): void
    {
        $pageEditor = new PageEditor;
        $pageEditor->rows = [
            'parent-row-id' => [
                'blocks' => [
                    'nested-row-1' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-1/2'],
                        'blocks' => [],
                    ],
                    'nested-row-2' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-1/2'],
                        'blocks' => [],
                    ],
                ],
                'properties' => ['desktopWidth' => 'w-full'],
            ],
        ];

        // Calculate component key before deletion
        $parentRow = $pageEditor->rows['parent-row-id'];
        $keyBefore = 'parent-row-id'.'-'.count($parentRow['blocks']);
        $this->assertEquals('parent-row-id-2', $keyBefore);

        // Delete a nested row
        $pageEditor->deleteRow('nested-row-1');

        // Calculate component key after deletion
        $parentRowAfter = $pageEditor->rows['parent-row-id'];
        $keyAfter = 'parent-row-id'.'-'.count($parentRowAfter['blocks']);
        $this->assertEquals('parent-row-id-1', $keyAfter);

        // Verify the key has changed, which will force component recreation
        $this->assertNotEquals($keyBefore, $keyAfter);

        // Verify the correct nested row was deleted
        $this->assertArrayNotHasKey('nested-row-1', $parentRowAfter['blocks']);
        $this->assertArrayHasKey('nested-row-2', $parentRowAfter['blocks']);
    }

    /** @test */
    public function component_key_accounts_for_all_blocks_deletion(): void
    {
        $pageEditor = new PageEditor;
        $pageEditor->rows = [
            'parent-row-id' => [
                'blocks' => [
                    'only-nested-row' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-full'],
                        'blocks' => [],
                    ],
                ],
                'properties' => ['desktopWidth' => 'w-full'],
            ],
        ];

        // Key before deletion
        $keyBefore = 'parent-row-id-1';

        // Delete the only nested row
        $pageEditor->deleteRow('only-nested-row');

        // Key after deletion
        $parentRowAfter = $pageEditor->rows['parent-row-id'];
        $keyAfter = 'parent-row-id'.'-'.count($parentRowAfter['blocks']);
        $this->assertEquals('parent-row-id-0', $keyAfter);

        // Verify key changed and all blocks are gone
        $this->assertNotEquals($keyBefore, $keyAfter);
        $this->assertEmpty($parentRowAfter['blocks']);
    }

    /** @test */
    public function component_key_formula_explanation(): void
    {
        // This test documents how the dynamic component key approach works:

        // Template uses: :key="$rowId . '-' . count($row['blocks'])"
        //
        // When deleteRow() is called:
        // 1. Backend data is updated (nested row removed from blocks array)
        // 2. Component key changes because count($row['blocks']) decreased
        // 3. Livewire sees different key and creates new component instance
        // 4. New instance receives updated blocks data (without deleted row)
        // 5. Template renders with deleted row gone from UI
        //
        // This avoids complex event handling between components and
        // relies on Livewire's built-in component lifecycle management

        $this->assertTrue(true, 'Component key approach documented');
    }
}
