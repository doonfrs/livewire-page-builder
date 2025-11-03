<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Support\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\SelectProperty;

class RowBlock extends Block
{
    public array $blocks = [];

    public ?string $rowId = null;

    public ?array $properties = null;

    public ?string $blockAlias = null;

    public $cssClasses;

    public $inlineStyles;

    public $flex = null;

    public $mobileWidth = 'w-full';

    public $tabletWidth = 'w-full';

    public $desktopWidth = 'w-full';

    public $selfCentered = true;

    public $contentWidthDesktop = 'w-full';

    public $contentWidthTablet = 'w-full';

    public $contentWidthMobile = 'w-full';

    public $mobileGap = null;

    public $tabletGap = null;

    public $desktopGap = null;

    public $contentAlign = 'content-center';

    public $isNested = false;

    public function mount()
    {
        Log::info('RowBlock::mount called', [
            'rowId' => $this->rowId,
            'hasProperties' => ! empty($this->properties),
            'propertiesCount' => $this->properties ? count($this->properties) : 0,
            'hasBlocks' => ! empty($this->blocks),
            'blocksCount' => count($this->blocks),
            'editMode' => $this->editMode ?? false,
            'timestamp' => now()->toISOString(),
        ]);

        // Ensure properties are properly initialized with saved values or defaults
        $defaultProperties = $this->getPropertyValues();

        Log::info('RowBlock property initialization', [
            'rowId' => $this->rowId,
            'defaultPropertiesCount' => count($defaultProperties),
            'passedPropertiesCount' => $this->properties ? count($this->properties) : 0,
            'passedProperties' => $this->properties ? json_encode($this->properties, JSON_PRETTY_PRINT) : 'null',
        ]);

        // If properties were passed from parent (e.g., BuilderBlock), merge them with defaults
        if ($this->properties) {
            $beforeMerge = $this->properties;
            $this->properties = array_merge($defaultProperties, $this->properties);

            Log::info('RowBlock properties merged', [
                'rowId' => $this->rowId,
                'beforeMerge' => json_encode($beforeMerge, JSON_PRETTY_PRINT),
                'afterMerge' => json_encode($this->properties, JSON_PRETTY_PRINT),
                'mergedPropertiesCount' => count($this->properties),
            ]);
        } else {
            $this->properties = $defaultProperties;

            Log::info('RowBlock properties set to defaults', [
                'rowId' => $this->rowId,
                'defaultPropertiesCount' => count($this->properties),
            ]);
        }

        $this->blocks = $this->blocks ?? [];
        $this->cssClasses = $this->makeClasses();
        $this->inlineStyles = $this->makeInlineStyles();

        Log::info('RowBlock::mount completed', [
            'rowId' => $this->rowId,
            'finalPropertiesCount' => count($this->properties),
            'finalBlocksCount' => count($this->blocks),
            'hasDesktopWidth' => isset($this->properties['desktopWidth']),
            'desktopWidth' => $this->properties['desktopWidth'] ?? 'not_set',
            'cssClasses' => $this->cssClasses,
        ]);
    }

    public function openBlockModal()
    {
        $this->dispatch('openBlockModal', $this->rowId);
    }

    public function addBlockToThisRow($blockAlias, $blockPageName = null, $beforeBlockId = null, $afterBlockId = null, $properties = null, $blocks = null)
    {
        $blockClass = app(PageBuilderService::class)->getClassNameFromAlias($blockAlias);
        if (! $blockClass) {
            return;
        }

        // Use provided properties or get defaults
        if ($properties === null) {
            $properties = app($blockClass)->getPropertyValues();
        }

        if ($blockPageName) {
            $properties['blockPageName'] = $blockPageName;
        }
        $blockId = uniqid();

        // Special handling for nested rows
        if ($blockClass === \Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock::class) {
            $block = [
                'alias' => $blockAlias,
                'properties' => $properties,
                'blocks' => $blocks ?? [], // Use provided blocks or empty array
            ];
        } else {
            $block = [
                'alias' => $blockAlias,
                'properties' => $properties,
            ];

            // Include blocks if provided (for any block type that might have nested blocks)
            if ($blocks !== null) {
                $block['blocks'] = $blocks;
            }
        }

        // Handle insertion position
        if ($beforeBlockId) {
            $blockIds = array_keys($this->blocks);
            $position = array_search($beforeBlockId, $blockIds);

            $newBlocks = [];
            foreach ($blockIds as $index => $id) {
                if ($index === $position) {
                    $newBlocks[$blockId] = $block; // Add new block before
                }
                $newBlocks[$id] = $this->blocks[$id]; // Add existing block
            }
            $this->blocks = $newBlocks;
        } elseif ($afterBlockId) {
            $blockIds = array_keys($this->blocks);
            $position = array_search($afterBlockId, $blockIds);

            // Create new array in the correct order
            $newBlocks = [];
            foreach ($blockIds as $index => $id) {
                $newBlocks[$id] = $this->blocks[$id]; // Add existing block
                if ($index === $position) {
                    $newBlocks[$blockId] = $block; // Add new block after
                }
            }
            $this->blocks = $newBlocks;
        } else {
            $this->blocks[$blockId] = $block;
        }

        // Sync changes back to parent structure
        $this->dispatch('sync-nested-row-data',
            nestedRowId: $this->rowId,
            blocks: $this->blocks
        );

        // Dispatch to notify that a block was added
        $this->dispatch(
            'block-added',
            rowId: $this->rowId,
            blockId: $blockId,
            blockAlias: $blockAlias,
            properties: $block['properties'],
            beforeBlockId: $beforeBlockId,
            afterBlockId: $afterBlockId
        );
    }

    #[On('add-block-to-nested-row')]
    public function addBlockToNestedRow($rowId, $blockAlias, $blockPageName = null, $beforeBlockId = null, $afterBlockId = null, $replaceBlockId = null, $properties = null, $blocks = null)
    {
        if ($rowId === $this->rowId) {
            // Handle replace operation - delete FIRST, then add
            if ($replaceBlockId) {
                // Find the next block after the one we're replacing
                $blockIds = array_keys($this->blocks);
                $replaceIndex = array_search($replaceBlockId, $blockIds);

                // Set position for new block
                if ($replaceIndex !== false && isset($blockIds[$replaceIndex + 1])) {
                    // There's a block after, insert before it
                    $beforeBlockId = $blockIds[$replaceIndex + 1];
                } else {
                    // No block after, will add at end
                    $beforeBlockId = null;
                    $afterBlockId = null;
                }

                // Delete the old block FIRST
                unset($this->blocks[$replaceBlockId]);
            }

            // Now add the new block (this will sync automatically)
            $this->addBlockToThisRow(
                blockAlias: $blockAlias,
                blockPageName: $blockPageName,
                beforeBlockId: $beforeBlockId,
                afterBlockId: $afterBlockId,
                properties: $properties,
                blocks: $blocks
            );
        }
    }

    #[On('updateBlockProperty')]
    public function updateBlockProperty($rowId, $blockId, $propertyName, $value)
    {
        // Handle row property updates (when this RowBlock is being treated as a row)
        if ($rowId == $this->rowId && ! $blockId) {
            $this->properties[$propertyName] = $value;
            $this->cssClasses = $this->makeClasses();
            $this->inlineStyles = $this->makeInlineStyles();

            // Force re-render to reflect property changes in the browser
            $this->dispatch('$refresh');

            return;
        }

        // Handle block property updates within this row
        if ($blockId && isset($this->blocks[$blockId])) {
            $this->blocks[$blockId]['properties'][$propertyName] = $value;

            // Sync changes back to parent structure
            $this->dispatch('sync-nested-row-data',
                nestedRowId: $this->rowId,
                blocks: $this->blocks
            );

            return;
        }
    }

    public function render()
    {
        $properties = $this->properties;
        $properties['editMode'] = $this->editMode;

        $this->flex = $properties['flex'] ?? null;
        if ($this->flex == 'none') {
            $this->flex = null;
        }

        $rowCssClasses = app(PageBuilderService::class)->getRowCssClassesFromProperties($properties);
        $dataAttributes = app(PageBuilderService::class)->getDataAttributesFromProperties($properties);

        if ($this->editMode) {
            return view('page-builder::livewire.builder.row', [
                'rowId' => $this->rowId,
                'properties' => $properties,
                'rowCssClasses' => $rowCssClasses,
                'dataAttributes' => $dataAttributes,
            ]);
        } else {
            return view('page-builder::livewire.builder.row-view', [
                'rowId' => $this->rowId,
                'properties' => $properties,
                'rowCssClasses' => $rowCssClasses,
                'dataAttributes' => $dataAttributes,
            ]);
        }
    }

    public function rowSelected()
    {
        $this->dispatch(
            'row-selected',
            rowId: $this->rowId,
            properties: $this->properties,
        );

        $this->skipRender();
    }

    #[On('edit-row')]
    public function editRow($rowId)
    {
        if ($rowId == $this->rowId) {
            $this->rowSelected();
        }
    }

    public function moveRowUp()
    {
        $this->dispatch('moveRowUp', $this->rowId)->to('page-editor');
    }

    public function moveRowDown()
    {
        $this->dispatch('moveRowDown', $this->rowId)->to('page-editor');
    }

    #[On('block-added')]
    public function blockAdded($rowId, $blockId, $blockAlias, $properties, $beforeBlockId = null, $afterBlockId = null, $blocks = null)
    {
        Log::info('🎯 RowBlock::blockAdded called', [
            'targetRowId' => $rowId,
            'thisRowId' => $this->rowId,
            'blockId' => $blockId,
            'blockAlias' => $blockAlias,
            'beforeBlockId' => $beforeBlockId,
            'afterBlockId' => $afterBlockId,
            'hasBlocks' => ! empty($blocks),
            'blocksCount' => $blocks ? count($blocks) : 0,
        ]);

        if ($rowId != $this->rowId) {
            Log::info('⏭️ Skipping - not for this row');

            return;
        }

        Log::info('✅ Processing block-added for this row');

        // Special handling for nested rows (check if alias contains 'row' or if blocks are provided)
        $isNestedRow = str_contains($blockAlias, 'row') || ! empty($blocks);

        if ($isNestedRow && ! empty($blocks)) {
            $block = [
                'alias' => $blockAlias,
                'properties' => $properties,
                'blocks' => $blocks, // Use provided blocks for nested row
            ];
            Log::info('✅ Created nested row block with nested blocks', [
                'alias' => $blockAlias,
                'blocksCount' => count($block['blocks']),
            ]);
        } elseif ($isNestedRow) {
            $block = [
                'alias' => $blockAlias,
                'properties' => $properties,
                'blocks' => [], // New nested row starts with empty blocks
            ];
            Log::info('✅ Created empty nested row block', [
                'alias' => $blockAlias,
            ]);
        } else {
            $block = [
                'alias' => $blockAlias,
                'properties' => $properties,
            ];
            Log::info('✅ Created regular block', [
                'alias' => $blockAlias,
            ]);
        }

        if ($beforeBlockId) {
            Log::info('📍 Inserting BEFORE block', ['beforeBlockId' => $beforeBlockId]);
            $blockIds = array_keys($this->blocks);
            $position = array_search($beforeBlockId, $blockIds);

            // Create new array in the correct order
            $newBlocks = [];
            foreach ($blockIds as $index => $id) {
                if ($index === $position) {
                    $newBlocks[$blockId] = $block; // Add new block before
                }
                $newBlocks[$id] = $this->blocks[$id]; // Add existing block
            }
            $this->blocks = $newBlocks;
        } elseif ($afterBlockId) {
            Log::info('📍 Inserting AFTER block', ['afterBlockId' => $afterBlockId]);
            $blockIds = array_keys($this->blocks);
            $position = array_search($afterBlockId, $blockIds);
            Log::info('📊 Position found', ['position' => $position, 'totalBlocks' => count($blockIds)]);

            // Create new array in the correct order
            $newBlocks = [];
            foreach ($blockIds as $index => $id) {
                $newBlocks[$id] = $this->blocks[$id]; // Add existing block
                if ($index === $position) {
                    $newBlocks[$blockId] = $block; // Add new block after
                }
            }
            $this->blocks = $newBlocks;
            Log::info('✅ Block added after position', ['newBlockCount' => count($this->blocks)]);
        } else {
            Log::info('📍 Appending block to end');
            $this->blocks[$blockId] = $block;
        }

        Log::info('🏁 RowBlock::blockAdded completed', [
            'totalBlocks' => count($this->blocks),
            'blockIds' => array_keys($this->blocks),
        ]);

        // Sync changes back to parent PageEditor structure
        Log::info('🔄 Syncing blocks back to PageEditor');
        $this->dispatch('sync-nested-row-data',
            nestedRowId: $this->rowId,
            blocks: $this->blocks
        );

        // The component will auto-render because $this->blocks changed
        // But we need to wait longer on the frontend for Livewire to finish rendering
        Log::info('✅ RowBlock will re-render automatically with new blocks');
    }

    #[On('deleteBlock')]
    public function deleteBlock($blockId)
    {
        if (isset($this->blocks[$blockId])) {
            unset($this->blocks[$blockId]);

            // Sync changes back to parent structure
            $this->dispatch('sync-nested-row-data',
                nestedRowId: $this->rowId,
                blocks: $this->blocks
            );
        }
    }

    public function makeClasses(): string
    {
        if ($this->isNested) {
            // For nested RowBlocks, don't include width/sizing properties as they're applied to the BuilderBlock wrapper
            // But ensure the nested row takes full width for proper control centering
            $propertiesWithoutSizing = $this->properties;

            // Force full width for nested rows to ensure controls are centered properly
            $propertiesWithoutSizing['mobileWidth'] = 'w-full';
            $propertiesWithoutSizing['tabletWidth'] = 'w-full';
            $propertiesWithoutSizing['desktopWidth'] = 'w-full';

            $classString = app(PageBuilderService::class)->getCssClassesFromProperties($propertiesWithoutSizing);
        } else {
            // For top-level RowBlocks, include all properties including width
            $classString = app(PageBuilderService::class)->getCssClassesFromProperties($this->properties);
        }

        return $classString;
    }

    public function makeInlineStyles(): string
    {
        $styleString = app(PageBuilderService::class)->getInlineStylesFromProperties($this->properties);

        return $styleString;
    }

    #[On('moveBlockUp')]
    public function moveBlockUp($blockId)
    {
        Log::info('RowBlock::moveBlockUp called', [
            'blockId' => $blockId,
            'rowId' => $this->rowId,
            'totalBlocks' => count($this->blocks),
            'currentOrder' => array_keys($this->blocks),
        ]);

        $blockIds = array_keys($this->blocks);
        $currentIndex = array_search($blockId, $blockIds);

        Log::info('Block movement analysis in RowBlock', [
            'currentIndex' => $currentIndex,
            'canMoveUp' => $currentIndex > 0,
        ]);

        if ($currentIndex > 0) {
            $newOrder = $blockIds;
            $temp = $newOrder[$currentIndex - 1];
            $newOrder[$currentIndex - 1] = $newOrder[$currentIndex];
            $newOrder[$currentIndex] = $temp;

            $this->blocks = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $this->blocks[$id]])->toArray();

            Log::info('Block moved up successfully in RowBlock', [
                'blockId' => $blockId,
                'rowId' => $this->rowId,
                'newOrder' => array_keys($this->blocks),
            ]);

            // Dispatch to PageEditor to sync the change
            $this->dispatch('syncBlockOrder', [
                'rowId' => $this->rowId,
                'blockOrder' => array_keys($this->blocks),
            ])->to('page-editor');
        } else {
            Log::info('Block cannot be moved up in RowBlock - already at top or not found', [
                'blockId' => $blockId,
                'rowId' => $this->rowId,
            ]);
        }
    }

    #[On('moveBlockDown')]
    public function moveBlockDown($blockId)
    {
        Log::info('RowBlock::moveBlockDown called', [
            'blockId' => $blockId,
            'rowId' => $this->rowId,
            'totalBlocks' => count($this->blocks),
            'currentOrder' => array_keys($this->blocks),
        ]);

        $blockIds = array_keys($this->blocks);
        $currentIndex = array_search($blockId, $blockIds);
        $lastIndex = count($blockIds) - 1;

        Log::info('Block movement analysis in RowBlock', [
            'currentIndex' => $currentIndex,
            'lastIndex' => $lastIndex,
            'canMoveDown' => $currentIndex !== false && $currentIndex < $lastIndex,
        ]);

        if ($currentIndex !== false && $currentIndex < $lastIndex) {
            $newOrder = $blockIds;
            $temp = $newOrder[$currentIndex + 1];
            $newOrder[$currentIndex + 1] = $newOrder[$currentIndex];
            $newOrder[$currentIndex] = $temp;

            $this->blocks = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $this->blocks[$id]])->toArray();

            Log::info('Block moved down successfully in RowBlock', [
                'blockId' => $blockId,
                'rowId' => $this->rowId,
                'newOrder' => array_keys($this->blocks),
            ]);

            // Dispatch to PageEditor to sync the change
            $this->dispatch('syncBlockOrder', [
                'rowId' => $this->rowId,
                'blockOrder' => array_keys($this->blocks),
            ])->to('page-editor');
        } else {
            Log::info('Block cannot be moved down in RowBlock - already at bottom or not found', [
                'blockId' => $blockId,
                'rowId' => $this->rowId,
            ]);
        }
    }

    public function getPageBuilderProperties(): array
    {
        $gapOptions = [
            '0' => '0',
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
            '6' => '6',
            '7' => '7',
            '8' => '8',
            '9' => '9',
            '10' => '10',
        ];

        return [
            new SelectProperty(
                name: 'flex',
                label: 'Flex',
                defaultValue: 'row',
                options: [
                    'none' => 'None',
                    'row' => 'Row',
                    'row-reverse' => 'Row Reverse',
                    'col' => 'Column',
                    'col-reverse' => 'Column Reverse',
                ],
            ),
            new SelectProperty(
                name: 'overflowX',
                label: 'Overflow X',
                defaultValue: '',
                options: [
                    '' => 'None',
                    'visible' => 'Visible',
                    'hidden' => 'Hidden',
                    'auto' => 'Auto',
                    'scroll' => 'Scroll',
                ],
            ),
            new SelectProperty(
                name: 'overflowY',
                label: 'Overflow Y',
                defaultValue: '',
                options: [
                    '' => 'None',
                    'visible' => 'Visible',
                    'hidden' => 'Hidden',
                    'auto' => 'Auto',
                    'scroll' => 'Scroll',
                ],
            ),
            new SelectProperty(
                name: 'contentAlign',
                label: 'Content Alignment',
                defaultValue: $this->contentAlign,
                options: [
                    'content-start' => 'Top',
                    'content-center' => 'Center',
                    'content-end' => 'Bottom',
                    'content-stretch' => 'Stretch',
                ],
            ),
            (new SelectProperty(
                name: 'contentWidthMobile',
                label: 'Mobile',
                defaultValue: $this->contentWidthMobile,
                options: $this->getPageBuilderWidthList(),
            ))->setGroup('contentWidth', 'Content Width', 3, 'heroicon-o-rectangle-group'),
            (new SelectProperty(
                name: 'contentWidthTablet',
                label: 'Tablet',
                defaultValue: $this->contentWidthTablet,
                options: $this->getPageBuilderWidthList(),
            ))->setGroup('contentWidth', 'Content Width', 3, 'heroicon-o-rectangle-group'),
            (new SelectProperty(
                name: 'contentWidthDesktop',
                label: 'Desktop',
                defaultValue: $this->contentWidthDesktop,
                options: $this->getPageBuilderWidthList(),
            ))->setGroup('contentWidth', 'Content Width', 3, 'heroicon-o-rectangle-group'),

            (new SelectProperty(
                name: 'mobileGap',
                label: 'Mobile',
                defaultValue: $this->mobileGap,
                options: $gapOptions,
            ))->setGroup('gap', 'Gap', 3, 'heroicon-o-rectangle-group'),
            (new SelectProperty(
                name: 'tabletGap',
                label: 'Tablet',
                defaultValue: $this->tabletGap,
                options: $gapOptions,
            ))->setGroup('gap', 'Gap', 3, 'heroicon-o-rectangle-group'),
            (new SelectProperty(
                name: 'desktopGap',
                label: 'Desktop',
                defaultValue: $this->desktopGap,
                options: $gapOptions,
            ))->setGroup('gap', 'Gap', 3, 'heroicon-o-rectangle-group'),
        ];
    }

    #[On('select-row')]
    public function selectRow($rowId)
    {
        if ($rowId != $this->rowId) {
            return;
        }
        $this->dispatch(
            'row-selected',
            rowId: $this->rowId,
            properties: $this->properties,
        );
    }

    public function copyRow()
    {
        $data = [
            'type' => 'RowBlock',
            'rowId' => $this->rowId,
            'properties' => $this->properties,
            'blocks' => $this->blocks,
            'blockAlias' => $this->blockAlias,
        ];

        $jsonData = json_encode($data);

        // Dispatch an event to copy to clipboard via JavaScript
        $this->dispatch('copy-to-clipboard', data: $jsonData);

        // Success notification
        $this->dispatch(
            'notify',
            message: __('Row copied to clipboard'),
            type: 'success'
        );
    }

    public function cutRow()
    {
        // First, copy the row to clipboard
        $data = [
            'type' => 'RowBlock',
            'rowId' => $this->rowId,
            'properties' => $this->properties,
            'blocks' => $this->blocks,
            'blockAlias' => $this->blockAlias,
        ];

        $jsonData = json_encode($data);

        // Dispatch an event to copy to clipboard via JavaScript
        $this->dispatch('copy-to-clipboard', data: $jsonData);

        // Then delete the row
        $this->dispatch('deleteRow', rowId: $this->rowId);

        // Success notification
        $this->dispatch(
            'notify',
            message: __('Row cut to clipboard'),
            type: 'success'
        );
    }

    public function getPageBuilderLabel(): string
    {
        return __('Row');
    }

    public function getPageBuilderIcon(): string
    {
        return 'heroicon-o-rectangle-group';
    }

    #[On('nested-row-deleted')]
    public function handleNestedRowDeleted($parentRowId, $deletedRowId, $updatedBlocks)
    {
        Log::info('RowBlock received nested-row-deleted event', [
            'thisRowId' => $this->rowId,
            'eventParentRowId' => $parentRowId,
            'deletedRowId' => $deletedRowId,
            'shouldUpdate' => $parentRowId === $this->rowId,
        ]);

        // Only update if this RowBlock is the parent of the deleted nested row
        if ($parentRowId === $this->rowId) {
            $this->blocks = $updatedBlocks;

            Log::info('RowBlock updated after nested row deletion', [
                'parentRowId' => $parentRowId,
                'deletedRowId' => $deletedRowId,
                'remainingBlocksCount' => count($this->blocks),
            ]);

            // Force Livewire to detect the state change and re-render
            $this->dispatch('$refresh');
        }
    }

    public function refreshBlocks($updatedBlocks)
    {
        Log::info('RowBlock refreshBlocks called via JavaScript', [
            'rowId' => $this->rowId,
            'newBlocksCount' => count($updatedBlocks),
            'updatedBlocks' => $updatedBlocks,
        ]);

        $this->blocks = $updatedBlocks;

        // Force a re-render of the component
        $this->skipRender(false);
    }

    /**
     * Duplicate this row (clone and place after current row with all its blocks).
     */
    public function duplicateRow()
    {
        Log::info('RowBlock::duplicateRow called', [
            'rowId' => $this->rowId,
            'blocksCount' => count($this->blocks),
            'isNested' => $this->isNested,
        ]);

        $data = [
            'rowId' => $this->rowId,
            'properties' => $this->properties,
            'blocks' => $this->blocks,
            'isNested' => $this->isNested,
        ];

        // Dispatch event to PageEditor to handle the duplication
        $this->dispatch('duplicateRow', data: $data);

        Log::info('RowBlock duplicate event dispatched', [
            'rowId' => $this->rowId,
        ]);
    }
}
