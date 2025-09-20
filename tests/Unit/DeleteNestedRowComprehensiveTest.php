<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class DeleteNestedRowComprehensiveTest extends TestCase
{
    /** @test */
    public function can_delete_nested_row_while_preserving_other_blocks(): void
    {
        $pageEditor = new PageEditor;

        // Create structure with multiple nested rows and regular blocks
        $pageEditor->rows = [
            'parent-row' => [
                'blocks' => [
                    'regular-block-1' => [
                        'alias' => 'some-regular-block',
                        'properties' => ['textColor' => '#000000'],
                    ],
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
                    'regular-block-2' => [
                        'alias' => 'another-regular-block',
                        'properties' => ['backgroundColor' => '#ffffff'],
                    ],
                ],
                'properties' => ['desktopWidth' => 'w-full'],
            ],
        ];

        // Delete one of the nested rows
        $pageEditor->deleteRow('nested-row-1');

        // Verify the correct nested row was deleted
        $blocks = $pageEditor->rows['parent-row']['blocks'];
        $this->assertArrayNotHasKey('nested-row-1', $blocks, 'Targeted nested row should be deleted');
        $this->assertArrayHasKey('nested-row-2', $blocks, 'Other nested row should remain');
        $this->assertArrayHasKey('regular-block-1', $blocks, 'Regular blocks should remain');
        $this->assertArrayHasKey('regular-block-2', $blocks, 'Regular blocks should remain');
        $this->assertCount(3, $blocks, 'Should have 3 blocks remaining');
    }

    /** @test */
    public function can_delete_nested_row_that_contains_blocks(): void
    {
        $pageEditor = new PageEditor;

        // Create nested row with its own blocks
        $pageEditor->rows = [
            'parent-row' => [
                'blocks' => [
                    'nested-row-with-content' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-full'],
                        'blocks' => [
                            'inner-block-1' => [
                                'alias' => 'inner-block-type',
                                'properties' => ['content' => 'Some content'],
                            ],
                            'inner-block-2' => [
                                'alias' => 'another-inner-block',
                                'properties' => ['style' => 'bold'],
                            ],
                        ],
                    ],
                ],
                'properties' => ['desktopWidth' => 'w-full'],
            ],
        ];

        // Delete the nested row that contains blocks
        $pageEditor->deleteRow('nested-row-with-content');

        // Verify everything is deleted
        $this->assertEmpty($pageEditor->rows['parent-row']['blocks'], 'Parent row blocks should be empty');
        $this->assertArrayHasKey('parent-row', $pageEditor->rows, 'Parent row should still exist');
    }

    /** @test */
    public function delete_nonexistent_row_does_not_cause_errors(): void
    {
        $pageEditor = new PageEditor;

        $pageEditor->rows = [
            'existing-row' => [
                'blocks' => [
                    'existing-block' => [
                        'alias' => 'some-block',
                        'properties' => [],
                    ],
                ],
                'properties' => [],
            ],
        ];

        // Try to delete a row that doesn't exist
        $pageEditor->deleteRow('nonexistent-row-id');

        // Structure should remain unchanged
        $this->assertArrayHasKey('existing-row', $pageEditor->rows);
        $this->assertArrayHasKey('existing-block', $pageEditor->rows['existing-row']['blocks']);
    }

    /** @test */
    public function delete_top_level_row_still_works(): void
    {
        $pageEditor = new PageEditor;

        $pageEditor->rows = [
            'top-level-1' => [
                'blocks' => [
                    'nested-row' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-full'],
                        'blocks' => [],
                    ],
                ],
                'properties' => ['desktopWidth' => 'w-full'],
            ],
            'top-level-2' => [
                'blocks' => [],
                'properties' => ['desktopWidth' => 'w-1/2'],
            ],
        ];

        // Delete a top-level row
        $pageEditor->deleteRow('top-level-1');

        // Verify top-level deletion still works
        $this->assertArrayNotHasKey('top-level-1', $pageEditor->rows, 'Deleted top-level row should be gone');
        $this->assertArrayHasKey('top-level-2', $pageEditor->rows, 'Other top-level row should remain');
        $this->assertCount(1, $pageEditor->rows, 'Should have 1 row remaining');
    }

    /** @test */
    public function delete_nested_row_from_multiple_parent_rows(): void
    {
        $pageEditor = new PageEditor;

        // Create multiple parent rows, each with nested rows
        $pageEditor->rows = [
            'parent-1' => [
                'blocks' => [
                    'nested-in-parent-1' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-full'],
                        'blocks' => [],
                    ],
                ],
                'properties' => [],
            ],
            'parent-2' => [
                'blocks' => [
                    'nested-in-parent-2' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-1/2'],
                        'blocks' => [],
                    ],
                    'target-nested-row' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['desktopWidth' => 'w-1/2'],
                        'blocks' => [],
                    ],
                ],
                'properties' => [],
            ],
        ];

        // Delete a specific nested row
        $pageEditor->deleteRow('target-nested-row');

        // Verify only the targeted nested row was deleted
        $this->assertArrayHasKey('nested-in-parent-1', $pageEditor->rows['parent-1']['blocks'], 'Nested row in parent-1 should remain');
        $this->assertArrayHasKey('nested-in-parent-2', $pageEditor->rows['parent-2']['blocks'], 'Other nested row in parent-2 should remain');
        $this->assertArrayNotHasKey('target-nested-row', $pageEditor->rows['parent-2']['blocks'], 'Target nested row should be deleted');
    }
}
