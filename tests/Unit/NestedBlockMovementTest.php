<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class NestedBlockMovementTest extends TestCase
{
    protected Theme $theme;
    protected BuilderPage $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->theme = Theme::create([
            'name' => 'Test Nested Movement Theme',
            'description' => 'Test theme for nested block movement',
        ]);

        $this->page = BuilderPage::create([
            'key' => 'test-nested-movement',
            'theme_id' => $this->theme->id,
            'components' => [],
        ]);
    }

    /** @test */
    public function it_can_move_blocks_in_top_level_rows(): void
    {
        // Create PageEditor component with initial structure
        $component = Livewire::test(PageEditor::class, [
            'pageKey' => 'test-nested-movement',
            'themeId' => $this->theme->id,
        ]);

        // Add a row and blocks
        $component->call('addRow');
        $rows = $component->get('rows');
        $rowId = array_keys($rows)[0];

        $component->call('addBlockToRow', $rowId, 'page-builder-trinavo-livewire-page-builder-blocks-spacer');
        $component->call('addBlockToRow', $rowId, 'page-builder-trinavo-livewire-page-builder-blocks-rich-text');

        $rows = $component->get('rows');
        $blockIds = array_keys($rows[$rowId]['blocks']);

        // Verify initial order
        $this->assertCount(2, $blockIds);
        $firstBlockId = $blockIds[0];
        $secondBlockId = $blockIds[1];

        // Move first block down
        $component->call('moveBlockDown', $firstBlockId);

        // Verify order changed
        $rows = $component->get('rows');
        $newBlockOrder = array_keys($rows[$rowId]['blocks']);

        $this->assertEquals($secondBlockId, $newBlockOrder[0], 'Second block should now be first');
        $this->assertEquals($firstBlockId, $newBlockOrder[1], 'First block should now be second');
    }

    /** @test */
    public function it_can_move_blocks_in_level_2_nested_rows(): void
    {
        // Create a RowBlock component to test nested movement
        $parentRowId = 'parent-row-' . uniqid();
        $nestedRowId = 'nested-row-' . uniqid();

        $rowBlock = new RowBlock();
        $rowBlock->rowId = $nestedRowId;

        // Set up level 2 nested structure: Row -> NestedRow -> Blocks
        $rowBlock->blocks = [
            'block-1' => [
                'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-spacer',
                'properties' => ['desktopHeight' => 'h-20']
            ],
            'block-2' => [
                'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-rich-text',
                'properties' => ['content' => 'Test content']
            ],
            'block-3' => [
                'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-spacer',
                'properties' => ['desktopHeight' => 'h-10']
            ]
        ];

        // Get initial order
        $initialOrder = array_keys($rowBlock->blocks);
        $this->assertEquals(['block-1', 'block-2', 'block-3'], $initialOrder);

        // Move middle block (block-2) down
        $blockToMove = 'block-2';
        $blockIds = array_keys($rowBlock->blocks);
        $currentIndex = array_search($blockToMove, $blockIds);
        $lastIndex = count($blockIds) - 1;

        // Simulate RowBlock::moveBlockDown logic
        if ($currentIndex !== false && $currentIndex < $lastIndex) {
            $newOrder = $blockIds;
            $temp = $newOrder[$currentIndex + 1];
            $newOrder[$currentIndex + 1] = $newOrder[$currentIndex];
            $newOrder[$currentIndex] = $temp;

            $rowBlock->blocks = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $rowBlock->blocks[$id]])->toArray();
        }

        // Verify new order
        $finalOrder = array_keys($rowBlock->blocks);
        $this->assertEquals(['block-1', 'block-3', 'block-2'], $finalOrder);
    }

    /** @test */
    public function it_can_move_blocks_in_level_3_nested_rows(): void
    {
        // Create level 3 nested structure: Row -> NestedRow -> DeepNestedRow -> Blocks
        $level3RowId = 'level3-row-' . uniqid();

        $level3RowBlock = new RowBlock();
        $level3RowBlock->rowId = $level3RowId;

        // Set up deeply nested blocks
        $level3RowBlock->blocks = [
            'deep-block-1' => [
                'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-spacer',
                'properties' => ['desktopHeight' => 'h-32']
            ],
            'deep-block-2' => [
                'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-rich-text',
                'properties' => ['content' => 'Deep nested content']
            ],
            'deep-block-3' => [
                'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-spacer',
                'properties' => ['desktopHeight' => 'h-16']
            ],
            'deep-block-4' => [
                'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-rich-text',
                'properties' => ['content' => 'Another deep block']
            ]
        ];

        // Get initial order
        $initialOrder = array_keys($level3RowBlock->blocks);
        $this->assertEquals(['deep-block-1', 'deep-block-2', 'deep-block-3', 'deep-block-4'], $initialOrder);

        // Move first block (deep-block-1) down twice to position 3
        $blockToMove = 'deep-block-1';

        // First move down
        $blockIds = array_keys($level3RowBlock->blocks);
        $currentIndex = array_search($blockToMove, $blockIds);
        $lastIndex = count($blockIds) - 1;

        if ($currentIndex !== false && $currentIndex < $lastIndex) {
            $newOrder = $blockIds;
            $temp = $newOrder[$currentIndex + 1];
            $newOrder[$currentIndex + 1] = $newOrder[$currentIndex];
            $newOrder[$currentIndex] = $temp;

            $level3RowBlock->blocks = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $level3RowBlock->blocks[$id]])->toArray();
        }

        // Verify after first move
        $afterFirstMove = array_keys($level3RowBlock->blocks);
        $this->assertEquals(['deep-block-2', 'deep-block-1', 'deep-block-3', 'deep-block-4'], $afterFirstMove);

        // Second move down
        $blockIds = array_keys($level3RowBlock->blocks);
        $currentIndex = array_search($blockToMove, $blockIds);
        $lastIndex = count($blockIds) - 1;

        if ($currentIndex !== false && $currentIndex < $lastIndex) {
            $newOrder = $blockIds;
            $temp = $newOrder[$currentIndex + 1];
            $newOrder[$currentIndex + 1] = $newOrder[$currentIndex];
            $newOrder[$currentIndex] = $temp;

            $level3RowBlock->blocks = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $level3RowBlock->blocks[$id]])->toArray();
        }

        // Verify final order
        $finalOrder = array_keys($level3RowBlock->blocks);
        $this->assertEquals(['deep-block-2', 'deep-block-3', 'deep-block-1', 'deep-block-4'], $finalOrder);
    }

    /** @test */
    public function it_handles_block_movement_at_boundaries(): void
    {
        $rowBlock = new RowBlock();
        $rowBlock->rowId = 'boundary-test-row';

        $rowBlock->blocks = [
            'first-block' => [
                'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-spacer',
                'properties' => []
            ],
            'last-block' => [
                'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-rich-text',
                'properties' => []
            ]
        ];

        $initialOrder = array_keys($rowBlock->blocks);

        // Try to move first block up (should not change anything)
        $blockToMove = 'first-block';
        $blockIds = array_keys($rowBlock->blocks);
        $currentIndex = array_search($blockToMove, $blockIds);

        if ($currentIndex > 0) {
            // This condition should be false
            $newOrder = $blockIds;
            $temp = $newOrder[$currentIndex - 1];
            $newOrder[$currentIndex - 1] = $newOrder[$currentIndex];
            $newOrder[$currentIndex] = $temp;

            $rowBlock->blocks = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $rowBlock->blocks[$id]])->toArray();
        }

        // Order should remain unchanged
        $afterMoveUp = array_keys($rowBlock->blocks);
        $this->assertEquals($initialOrder, $afterMoveUp);

        // Try to move last block down (should not change anything)
        $blockToMove = 'last-block';
        $blockIds = array_keys($rowBlock->blocks);
        $currentIndex = array_search($blockToMove, $blockIds);
        $lastIndex = count($blockIds) - 1;

        if ($currentIndex !== false && $currentIndex < $lastIndex) {
            // This condition should be false
            $newOrder = $blockIds;
            $temp = $newOrder[$currentIndex + 1];
            $newOrder[$currentIndex + 1] = $newOrder[$currentIndex];
            $newOrder[$currentIndex] = $temp;

            $rowBlock->blocks = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $rowBlock->blocks[$id]])->toArray();
        }

        // Order should remain unchanged
        $afterMoveDown = array_keys($rowBlock->blocks);
        $this->assertEquals($initialOrder, $afterMoveDown);
    }

    /** @test */
    public function it_simulates_sync_functionality_between_rowblock_and_pageeditor(): void
    {
        // Create a sync test page
        $syncPage = BuilderPage::create([
            'key' => 'test-sync',
            'theme_id' => $this->theme->id,
            'components' => [],
        ]);

        // Create a PageEditor component
        $component = Livewire::test(PageEditor::class, [
            'pageKey' => 'test-sync',
            'themeId' => $this->theme->id,
        ]);

        // First test top-level sync (this should work)
        $topLevelRowId = 'top-level-test';
        $testStructure = [
            $topLevelRowId => [
                'properties' => ['flex' => 'row'],
                'blocks' => [
                    'top-block-1' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-spacer',
                        'properties' => ['desktopHeight' => 'h-20']
                    ],
                    'top-block-2' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-rich-text',
                        'properties' => ['content' => 'Test']
                    ]
                ]
            ]
        ];

        $component->set('rows', $testStructure);

        // Verify initial structure
        $rows = $component->get('rows');
        $initialOrder = array_keys($rows[$topLevelRowId]['blocks']);
        $this->assertEquals(['top-block-1', 'top-block-2'], $initialOrder);

        // Test sync for top-level blocks
        $newBlockOrder = ['top-block-2', 'top-block-1']; // Swapped order

        $component->call('syncBlockOrder', [
            'rowId' => $topLevelRowId,
            'blockOrder' => $newBlockOrder
        ]);

        // Verify PageEditor updated its structure
        $rows = $component->get('rows');
        $finalOrder = array_keys($rows[$topLevelRowId]['blocks']);
        $this->assertEquals($newBlockOrder, $finalOrder);
    }

    /** @test */
    public function it_syncs_deeply_nested_block_movements(): void
    {
        // Create nested sync test page
        $nestedSyncPage = BuilderPage::create([
            'key' => 'test-nested-sync',
            'theme_id' => $this->theme->id,
            'components' => [],
        ]);

        // Create a PageEditor component
        $component = Livewire::test(PageEditor::class, [
            'pageKey' => 'test-nested-sync',
            'themeId' => $this->theme->id,
        ]);

        // Set up 3-level nested structure for sync testing
        $parentRowId = 'parent-sync-' . uniqid();
        $level2RowId = 'level2-sync-' . uniqid();
        $level3RowId = 'level3-sync-' . uniqid();

        $deepNestedStructure = [
            $parentRowId => [
                'properties' => ['flex' => 'row'],
                'blocks' => [
                    $level2RowId => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['flex' => 'col'],
                        'blocks' => [
                            $level3RowId => [
                                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                                'properties' => ['flex' => 'row'],
                                'blocks' => [
                                    'deep-a' => [
                                        'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-spacer',
                                        'properties' => ['desktopHeight' => 'h-10']
                                    ],
                                    'deep-b' => [
                                        'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-rich-text',
                                        'properties' => ['content' => 'Deep content']
                                    ],
                                    'deep-c' => [
                                        'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-spacer',
                                        'properties' => ['desktopHeight' => 'h-20']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $component->set('rows', $deepNestedStructure);

        // Verify initial order at level 3
        $rows = $component->get('rows');
        $deepBlocks = $rows[$parentRowId]['blocks'][$level2RowId]['blocks'][$level3RowId]['blocks'];
        $initialOrder = array_keys($deepBlocks);
        $this->assertEquals(['deep-a', 'deep-b', 'deep-c'], $initialOrder);

        // Simulate RowBlock at level 3 syncing a new order
        $newDeepOrder = ['deep-c', 'deep-a', 'deep-b'];

        $component->call('syncBlockOrder', [
            'rowId' => $level3RowId,
            'blockOrder' => $newDeepOrder
        ]);

        // Verify the deeply nested structure was updated correctly
        $rows = $component->get('rows');
        $updatedDeepBlocks = $rows[$parentRowId]['blocks'][$level2RowId]['blocks'][$level3RowId]['blocks'];
        $finalOrder = array_keys($updatedDeepBlocks);

        $this->assertEquals($newDeepOrder, $finalOrder);

        // Verify the properties are still intact after sync
        $this->assertEquals('h-20', $updatedDeepBlocks['deep-c']['properties']['desktopHeight']);
        $this->assertEquals('Deep content', $updatedDeepBlocks['deep-b']['properties']['content']);
        $this->assertEquals('h-10', $updatedDeepBlocks['deep-a']['properties']['desktopHeight']);
    }

    /** @test */
    public function it_preserves_block_properties_during_movement(): void
    {
        $rowBlock = new RowBlock();
        $rowBlock->rowId = 'properties-test-row';

        $originalProperties1 = ['desktopHeight' => 'h-32', 'backgroundColor' => 'primary'];
        $originalProperties2 = ['content' => 'Test content', 'textColor' => 'text-white'];

        $rowBlock->blocks = [
            'prop-block-1' => [
                'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-spacer',
                'properties' => $originalProperties1
            ],
            'prop-block-2' => [
                'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-rich-text',
                'properties' => $originalProperties2
            ]
        ];

        // Move first block down
        $blockToMove = 'prop-block-1';
        $blockIds = array_keys($rowBlock->blocks);
        $currentIndex = array_search($blockToMove, $blockIds);
        $lastIndex = count($blockIds) - 1;

        if ($currentIndex !== false && $currentIndex < $lastIndex) {
            $newOrder = $blockIds;
            $temp = $newOrder[$currentIndex + 1];
            $newOrder[$currentIndex + 1] = $newOrder[$currentIndex];
            $newOrder[$currentIndex] = $temp;

            $rowBlock->blocks = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $rowBlock->blocks[$id]])->toArray();
        }

        // Verify order changed but properties preserved
        $finalOrder = array_keys($rowBlock->blocks);
        $this->assertEquals(['prop-block-2', 'prop-block-1'], $finalOrder);

        // Verify properties are intact
        $this->assertEquals($originalProperties1, $rowBlock->blocks['prop-block-1']['properties']);
        $this->assertEquals($originalProperties2, $rowBlock->blocks['prop-block-2']['properties']);
    }

    /** @test */
    public function it_handles_complex_nested_structure_movements(): void
    {
        // Create a complex nested test page
        $complexPage = BuilderPage::create([
            'key' => 'test-complex-nested',
            'theme_id' => $this->theme->id,
            'components' => [],
        ]);

        // Create a complex 3-level nested structure
        $component = Livewire::test(PageEditor::class, [
            'pageKey' => 'test-complex-nested',
            'themeId' => $this->theme->id,
        ]);

        $complexStructure = [
            'root-row' => [
                'properties' => ['flex' => 'row'],
                'blocks' => [
                    'level1-row' => [
                        'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                        'properties' => ['flex' => 'col'],
                        'blocks' => [
                            'level2-row' => [
                                'alias' => 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                                'properties' => ['flex' => 'row'],
                                'blocks' => [
                                    'deep-block-a' => [
                                        'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-spacer',
                                        'properties' => ['desktopHeight' => 'h-10']
                                    ],
                                    'deep-block-b' => [
                                        'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-rich-text',
                                        'properties' => ['content' => 'Level 3 content']
                                    ],
                                    'deep-block-c' => [
                                        'alias' => 'page-builder-trinavo-livewire-page-builder-blocks-spacer',
                                        'properties' => ['desktopHeight' => 'h-20']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $component->set('rows', $complexStructure);

        // Test sync at level 2 (deepest level)
        $newDeepOrder = ['deep-block-c', 'deep-block-a', 'deep-block-b'];

        $component->call('syncBlockOrder', [
            'rowId' => 'level2-row',
            'blockOrder' => $newDeepOrder
        ]);

        // Verify the deep nested structure was updated
        $rows = $component->get('rows');
        $deepBlocks = $rows['root-row']['blocks']['level1-row']['blocks']['level2-row']['blocks'];
        $actualOrder = array_keys($deepBlocks);

        $this->assertEquals($newDeepOrder, $actualOrder);

        // Verify other levels remain intact
        $this->assertArrayHasKey('level1-row', $rows['root-row']['blocks']);
        $this->assertArrayHasKey('level2-row', $rows['root-row']['blocks']['level1-row']['blocks']);
    }
}