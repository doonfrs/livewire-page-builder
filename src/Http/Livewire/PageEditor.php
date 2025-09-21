<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Support\ThemeResolver;

class PageEditor extends Component
{
    use ThemeResolver;

    public $rows = [];

    public $availableBlocks = [];

    public bool $showBlockModal = false;

    public bool $showPageBlocksModal = false;

    public string $blockFilter = '';

    public ?string $modalRowId = null;

    public ?string $beforeBlockId = null;

    public ?string $afterBlockId = null;

    public ?string $pageKey = null;

    public ?int $themeId = null;

    public ?Theme $currentTheme = null;

    public $availableThemes = [];

    public bool $showThemeSelector = false;

    public $selfCentered = false;

    public BuilderPage $page;

    public function mount()
    {
        $this->availableBlocks = app(PageBuilderService::class)->getAvailableBlocks();
        $this->availableThemes = Theme::orderBy('name')->get()->toArray();

        $this->pageKey = request()->route('pageKey');
        // Only depend on route param; no session fallback here
        $this->themeId = request()->route('themeId');

        // If still no theme (and themes exist), show theme selector
        if (! $this->themeId && count($this->availableThemes) > 0) {
            $this->showThemeSelector = true;

            return;
        }

        // Load current theme when provided
        if ($this->themeId) {
            $this->currentTheme = Theme::find($this->themeId);
        }

        // Create or find the page
        $this->page = BuilderPage::firstOrCreate([
            'key' => $this->pageKey,
            'theme_id' => $this->themeId,
        ]);

        Log::info('PageEditor page loaded', [
            'pageId' => $this->page->id,
            'pageKey' => $this->page->key,
            'themeId' => $this->page->theme_id,
            'hasComponents' => ! empty($this->page->components),
            'componentsCount' => $this->page->components ? count($this->page->components) : 0,
            'rawComponents' => $this->page->components ? json_encode($this->page->components, JSON_PRETTY_PRINT) : 'null',
            'timestamp' => now()->toISOString(),
        ]);

        $this->rows = $this->page->components ? $this->page->components : [];

        Log::info('PageEditor rows initialized', [
            'pageId' => $this->page->id,
            'rowsCount' => count($this->rows),
            'rowIds' => array_keys($this->rows),
            'detailedStructure' => $this->getDetailedComponentStructure(),
        ]);
    }

    #[On('save-page')]
    public function savePage()
    {
        Log::info('PageEditor::savePage called', [
            'pageKey' => $this->pageKey,
            'themeId' => $this->themeId,
            'pageExists' => isset($this->page) && $this->page,
            'rowsCount' => count($this->rows),
            'timestamp' => now()->toISOString(),
        ]);

        // Ensure we have a page to save to
        if (! isset($this->page) || ! $this->page) {
            // If page doesn't exist but we have the required data, create it
            if ($this->pageKey && $this->themeId) {
                Log::info('Creating new page', [
                    'pageKey' => $this->pageKey,
                    'themeId' => $this->themeId,
                ]);

                $this->page = BuilderPage::firstOrCreate([
                    'key' => $this->pageKey,
                    'theme_id' => $this->themeId,
                ]);

                Log::info('Page created/found', [
                    'pageId' => $this->page->id,
                    'pageKey' => $this->page->key,
                ]);
            } else {
                Log::warning('Cannot save page - missing pageKey or themeId', [
                    'pageKey' => $this->pageKey,
                    'themeId' => $this->themeId,
                ]);

                // Cannot save without pageKey and themeId
                return;
            }
        }

        // Log detailed structure before saving
        Log::info('Saving page components', [
            'pageId' => $this->page->id,
            'pageKey' => $this->page->key,
            'componentStructure' => $this->getDetailedComponentStructure(),
            'rawRowsData' => json_encode($this->rows, JSON_PRETTY_PRINT),
        ]);

        $this->page->components = $this->rows;
        $this->page->saveOrFail();

        Log::info('Page saved successfully', [
            'pageId' => $this->page->id,
            'pageKey' => $this->page->key,
            'savedComponentsCount' => count($this->page->components),
        ]);
    }

    private function getDetailedComponentStructure(): array
    {
        $structure = [];
        foreach ($this->rows as $rowId => $row) {
            $structure[$rowId] = [
                'properties' => $row['properties'] ?? [],
                'blocks' => [],
            ];

            if (isset($row['blocks'])) {
                foreach ($row['blocks'] as $blockId => $block) {
                    $structure[$rowId]['blocks'][$blockId] = [
                        'alias' => $block['alias'] ?? 'unknown',
                        'properties' => $block['properties'] ?? [],
                        'hasNestedBlocks' => isset($block['blocks']) && is_array($block['blocks']),
                        'nestedBlocksCount' => isset($block['blocks']) ? count($block['blocks']) : 0,
                    ];
                }
            }
        }

        return $structure;
    }

    public function selectThemeForPage($themeId)
    {
        $theme = Theme::find($themeId);
        if (! $theme) {
            return;
        }

        // Redirect to builder with theme in URL; no session persistence
        return redirect()->route('page-builder.editor', [
            'pageKey' => $this->pageKey,
            'themeId' => $themeId,
        ]);
    }

    public function openThemeSelector()
    {
        $this->showThemeSelector = true;
    }

    public function closeThemeSelector()
    {
        $this->showThemeSelector = false;
    }

    public function switchTheme($themeId)
    {
        if ($this->themeId == $themeId) {
            return;
        }

        $theme = Theme::find($themeId);
        if (! $theme) {
            return;
        }

        // Save current page before switching
        if (isset($this->page)) {
            $this->savePage();
        }

        // Redirect to updated URL containing new theme ID
        return redirect()->route('page-builder.editor', [
            'pageKey' => $this->pageKey,
            'themeId' => $themeId,
        ]);
    }

    #[On('addRow')]
    public function addRow($afterRowId = null, $beforeRowId = null)
    {
        $rowId = uniqid();
        $rowBlock = app(RowBlock::class);
        $row = [
            'blocks' => [],
            'properties' => $rowBlock->getPropertyValues(),
        ];
        if ($afterRowId) {
            $afterRowIndex = array_search($afterRowId, array_keys($this->rows)) + 1;
            $this->rows = array_merge(
                array_slice($this->rows, 0, $afterRowIndex),
                [$rowId => $row],
                array_slice($this->rows, $afterRowIndex)
            );
        } elseif ($beforeRowId) {
            $beforeRowIndex = array_search($beforeRowId, array_keys($this->rows));
            $this->rows = array_merge(
                array_slice($this->rows, 0, $beforeRowIndex),
                [$rowId => $row],
                array_slice($this->rows, $beforeRowIndex)
            );
        } else {
            $this->rows[$rowId] = $row;
        }

        $this->dispatch(
            'row-added',
            rowId: $rowId,
            properties: $row['properties']
        );
    }

    public function addBlockToRow($rowId, $blockAlias, $blockPageName = null)
    {
        $blockClass = null;
        foreach ($this->availableBlocks as $block) {
            if ($block['alias'] === $blockAlias) {
                $blockClass = $block['class'];
                break;
            }
        }

        if (! $blockClass) {
            return;
        }

        $properties = app($blockClass)->getPropertyValues();
        if ($blockPageName) {
            $properties['blockPageName'] = $blockPageName;
        }
        $blockId = uniqid();

        // Special handling for nested rows
        if ($blockClass === \Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock::class) {
            $block = [
                'alias' => $blockAlias,
                'properties' => $properties,
                'blocks' => [], // Nested rows start with empty blocks
            ];
        } else {
            $block = [
                'alias' => $blockAlias,
                'properties' => $properties,
            ];
        }

        if ($this->beforeBlockId) {
            $blockIds = array_keys($this->rows[$rowId]['blocks']);
            $position = array_search($this->beforeBlockId, $blockIds);

            $newBlocks = [];
            foreach ($blockIds as $index => $id) {
                if ($index === $position) {
                    $newBlocks[$blockId] = $block; // Add new block before
                }
                $newBlocks[$id] = $this->rows[$rowId]['blocks'][$id]; // Add existing block
            }
            $this->rows[$rowId]['blocks'] = $newBlocks;
        } elseif ($this->afterBlockId) {
            $blockIds = array_keys($this->rows[$rowId]['blocks']);
            $position = array_search($this->afterBlockId, $blockIds);

            // Create new array in the correct order
            $newBlocks = [];
            foreach ($blockIds as $index => $id) {
                $newBlocks[$id] = $this->rows[$rowId]['blocks'][$id]; // Add existing block
                if ($index === $position) {
                    $newBlocks[$blockId] = $block; // Add new block after
                }
            }
            $this->rows[$rowId]['blocks'] = $newBlocks;
        } else {
            $this->rows[$rowId]['blocks'][$blockId] = $block;
        }

        // Dispatch to row-block component
        $this->dispatch(
            'block-added',
            rowId: $rowId,
            blockId: $blockId,
            blockAlias: $blockAlias,
            properties: $block['properties'],
            beforeBlockId: $this->beforeBlockId,
            afterBlockId: $this->afterBlockId
        );
    }

    #[On('openBlockModal')]
    public function openBlockModal($rowId, $beforeBlockId = null, $afterBlockId = null)
    {
        $this->showBlockModal = true;
        $this->blockFilter = '';
        $this->modalRowId = $rowId;
        $this->beforeBlockId = $beforeBlockId;
        $this->afterBlockId = $afterBlockId;
    }

    public function closeBlockModal()
    {
        $this->showBlockModal = false;
        $this->modalRowId = null;
        $this->beforeBlockId = null;
        $this->afterBlockId = null;
    }

    #[On('openPageBlocksModal')]
    public function openPageBlocksModal()
    {
        $this->showPageBlocksModal = true;
    }

    public function closePageBlocksModal()
    {
        $this->showPageBlocksModal = false;
    }

    public function getFilteredBlocksProperty()
    {
        if (! $this->blockFilter) {
            return $this->availableBlocks;
        }
        $filter = strtolower($this->blockFilter);

        return array_values(array_filter($this->availableBlocks, function ($block) use ($filter) {
            return str_contains(strtolower($block['label']), $filter);
        }));
    }

    public function getAllBlocksProperty()
    {
        // Prepare blocks data for Alpine.js, including HTML icon representation
        return collect($this->availableBlocks)->map(function ($block) {
            // Get the icon component name and convert it to HTML
            $iconComponent = $block['icon'] ?? 'heroicon-o-cube';

            // Return block data with icon component name
            return [
                'alias' => $block['alias'],
                'label' => $block['label'],
                'blockPageName' => $block['blockPageName'] ?? null,
                'icon' => $iconComponent,
            ];
        })->values()->toArray();
    }

    public function addBlockToModalRow($blockAlias, $blockPageName = null)
    {
        if ($this->modalRowId) {
            // Check if this is a top-level row
            if (isset($this->rows[$this->modalRowId])) {
                $this->addBlockToRow($this->modalRowId, $blockAlias, $blockPageName);
            } else {
                // This is a nested row, dispatch event for RowBlock components to handle
                $this->dispatch('add-block-to-nested-row',
                    rowId: $this->modalRowId,
                    blockAlias: $blockAlias,
                    blockPageName: $blockPageName
                );
            }
            $this->closeBlockModal();
        }
    }

    #[On('sync-nested-row-data')]
    public function syncNestedRowData($nestedRowId, $blocks)
    {
        // Find the nested row in the structure and update its blocks
        $this->updateNestedRowBlocks($this->rows, $nestedRowId, $blocks);
    }

    private function updateNestedRowBlocks(&$structure, $nestedRowId, $blocks)
    {
        foreach ($structure as $rowId => $row) {
            if (isset($row['blocks'])) {
                foreach ($row['blocks'] as $blockId => $block) {
                    // Check if this block is the nested row we're looking for
                    if ($blockId === $nestedRowId) {
                        $structure[$rowId]['blocks'][$blockId]['blocks'] = $blocks;

                        return true;
                    }

                    // If this block has nested blocks, recursively search
                    if (isset($block['blocks'])) {
                        $nestedBlocks = &$structure[$rowId]['blocks'][$blockId]['blocks'];
                        if ($this->updateNestedRowBlocks($nestedBlocks, $nestedRowId, $blocks)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    #[On('updateBlockProperty')]
    public function updateBlockProperty($rowId, $blockId, $propertyName, $value)
    {
        Log::info('PageEditor::updateBlockProperty called', [
            'rowId' => $rowId,
            'blockId' => $blockId,
            'propertyName' => $propertyName,
            'value' => $value,
            'timestamp' => now()->toISOString(),
        ]);

        if ($rowId && ! $blockId) {
            // Updating row properties - handle both top-level and nested rows
            Log::info('Updating row property', [
                'rowId' => $rowId,
                'propertyName' => $propertyName,
                'value' => $value,
                'isTopLevelRow' => isset($this->rows[$rowId]),
            ]);

            if (isset($this->rows[$rowId])) {
                // Top-level row
                $this->rows[$rowId]['properties'][$propertyName] = $value;
                Log::info('Top-level row property updated successfully', [
                    'rowId' => $rowId,
                    'propertyName' => $propertyName,
                    'value' => $value,
                ]);
            } else {
                // Check if it's a nested row
                $nestedRowFound = false;
                foreach ($this->rows as $parentRowId => $parentRow) {
                    if (isset($parentRow['blocks'][$rowId])) {
                        // Found the nested row
                        $this->rows[$parentRowId]['blocks'][$rowId]['properties'][$propertyName] = $value;
                        $nestedRowFound = true;
                        Log::info('Nested row property updated successfully', [
                            'parentRowId' => $parentRowId,
                            'nestedRowId' => $rowId,
                            'propertyName' => $propertyName,
                            'value' => $value,
                        ]);
                        break;
                    }
                }

                if (! $nestedRowFound) {
                    Log::warning('Row not found (neither top-level nor nested)', ['rowId' => $rowId]);
                }
            }
        } else {
            // Updating block properties
            Log::info('Updating block property', [
                'rowId' => $rowId,
                'blockId' => $blockId,
                'propertyName' => $propertyName,
                'value' => $value,
            ]);

            if ($rowId && $blockId) {
                // If both rowId and blockId are provided, ensure the block is actually in that row
                Log::info('Checking specific row for block', [
                    'rowId' => $rowId,
                    'blockId' => $blockId,
                    'rowExists' => isset($this->rows[$rowId]),
                    'blockExists' => isset($this->rows[$rowId]['blocks'][$blockId]),
                ]);

                if (isset($this->rows[$rowId]['blocks'][$blockId])) {
                    $oldValue = $this->rows[$rowId]['blocks'][$blockId]['properties'][$propertyName] ?? 'not_set';
                    $this->rows[$rowId]['blocks'][$blockId]['properties'][$propertyName] = $value;

                    Log::info('Block property updated successfully', [
                        'rowId' => $rowId,
                        'blockId' => $blockId,
                        'propertyName' => $propertyName,
                        'oldValue' => $oldValue,
                        'newValue' => $value,
                        'blockAlias' => $this->rows[$rowId]['blocks'][$blockId]['alias'] ?? 'unknown',
                    ]);
                } else {
                    Log::warning('Block not found in specified row', [
                        'rowId' => $rowId,
                        'blockId' => $blockId,
                        'availableRows' => array_keys($this->rows),
                        'blocksInRow' => isset($this->rows[$rowId]) ? array_keys($this->rows[$rowId]['blocks'] ?? []) : [],
                    ]);
                }
            } else {
                // Fallback: search all rows for the block (maintain backward compatibility)
                Log::info('Searching all rows for block', ['blockId' => $blockId]);

                $found = false;
                foreach ($this->rows as $rId => $row) {
                    if (isset($row['blocks'][$blockId])) {
                        $oldValue = $this->rows[$rId]['blocks'][$blockId]['properties'][$propertyName] ?? 'not_set';
                        $this->rows[$rId]['blocks'][$blockId]['properties'][$propertyName] = $value;

                        Log::info('Block property updated via fallback search', [
                            'foundInRowId' => $rId,
                            'blockId' => $blockId,
                            'propertyName' => $propertyName,
                            'oldValue' => $oldValue,
                            'newValue' => $value,
                            'blockAlias' => $this->rows[$rId]['blocks'][$blockId]['alias'] ?? 'unknown',
                        ]);

                        $found = true;
                        break;
                    }
                }

                if (! $found) {
                    Log::warning('Block not found in any row', [
                        'blockId' => $blockId,
                        'availableRows' => array_keys($this->rows),
                        'allBlockIds' => $this->getAllBlockIds(),
                    ]);
                }
            }
        }
        $this->skipRender();
    }

    private function getAllBlockIds(): array
    {
        $allBlockIds = [];
        foreach ($this->rows as $rowId => $row) {
            if (isset($row['blocks'])) {
                foreach ($row['blocks'] as $blockId => $block) {
                    $allBlockIds[] = [
                        'rowId' => $rowId,
                        'blockId' => $blockId,
                        'alias' => $block['alias'] ?? 'unknown',
                    ];
                }
            }
        }

        return $allBlockIds;
    }

    #[On('moveRowUp')]
    public function moveRowUp($rowId)
    {
        $rowIds = array_keys($this->rows);
        $currentIndex = array_search($rowId, $rowIds);

        if ($currentIndex > 0) {
            $newOrder = $rowIds;
            $temp = $newOrder[$currentIndex - 1];
            $newOrder[$currentIndex - 1] = $newOrder[$currentIndex];
            $newOrder[$currentIndex] = $temp;

            $this->rows = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $this->rows[$id]])->toArray();
        }
    }

    #[On('moveRowDown')]
    public function moveRowDown($rowId)
    {
        $rowIds = array_keys($this->rows);
        $currentIndex = array_search($rowId, $rowIds);
        if ($currentIndex < count($this->rows) - 1) {
            $newOrder = $rowIds;
            $temp = $newOrder[$currentIndex + 1];
            $newOrder[$currentIndex + 1] = $newOrder[$currentIndex];
            $newOrder[$currentIndex] = $temp;

            $this->rows = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $this->rows[$id]])->toArray();
        }
    }

    #[On('moveBlockUp')]
    public function moveBlockUp($blockId)
    {
        foreach ($this->rows as $rowId => $row) {
            if (isset($row['blocks'][$blockId])) {
                $blockIds = array_keys($row['blocks']);
                $currentIndex = array_search($blockId, $blockIds);

                if ($currentIndex > 0) {
                    $newOrder = $blockIds;
                    $temp = $newOrder[$currentIndex - 1];
                    $newOrder[$currentIndex - 1] = $newOrder[$currentIndex];
                    $newOrder[$currentIndex] = $temp;

                    $this->rows[$rowId]['blocks'] = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $row['blocks'][$id]])->toArray();
                }
            }
        }

        $this->skipRender();
    }

    #[On('moveBlockDown')]
    public function moveBlockDown($blockId)
    {
        foreach ($this->rows as $rowId => $row) {
            if (isset($row['blocks'][$blockId])) {
                $blockIds = array_keys($row['blocks']);
                $currentIndex = array_search($blockId, $blockIds);
                if ($currentIndex < count($row['blocks']) - 1) {
                    $newOrder = $blockIds;
                    $temp = $newOrder[$currentIndex + 1];
                    $newOrder[$currentIndex + 1] = $newOrder[$currentIndex];
                    $newOrder[$currentIndex] = $temp;

                    $this->rows[$rowId]['blocks'] = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $row['blocks'][$id]])->toArray();
                }
            }
        }

        $this->skipRender();
    }

    #[On('deleteRow')]
    public function deleteRow($rowId)
    {
        // First check if it's a top-level row
        if (isset($this->rows[$rowId])) {
            unset($this->rows[$rowId]);

            return;
        }

        // If not found as top-level row, search for it as a nested row in blocks
        foreach ($this->rows as $parentRowId => $parentRow) {
            if (isset($parentRow['blocks'][$rowId])) {
                unset($this->rows[$parentRowId]['blocks'][$rowId]);
                Log::info('Nested row deleted successfully', [
                    'parentRowId' => $parentRowId,
                    'deletedNestedRowId' => $rowId,
                ]);

                // Notify the parent RowBlock component to update its blocks
                $this->dispatch('nested-row-deleted',
                    parentRowId: $parentRowId,
                    deletedRowId: $rowId,
                    updatedBlocks: $this->rows[$parentRowId]['blocks']
                );

                Log::info('PageEditor nested row deletion completed - dispatched nested-row-deleted event', [
                    'parentRowId' => $parentRowId,
                    'deletedRowId' => $rowId,
                    'remainingBlocksCount' => count($this->rows[$parentRowId]['blocks']),
                ]);

                return;
            }
        }

        Log::warning('Row not found for deletion', ['rowId' => $rowId]);
    }

    #[On('deleteBlock')]
    public function deleteBlock($blockId)
    {
        foreach ($this->rows as $rowId => $row) {
            if (isset($row['blocks'][$blockId])) {
                unset($this->rows[$rowId]['blocks'][$blockId]);
                break;
            }
        }
    }

    /**
     * Handle pasting clipboard data
     */
    #[On('paste-from-clipboard')]
    public function pasteFromClipboard(
        $clipboardData,
        $targetRowId = null,
        $targetBlockId = null,
        $position = 'after')
    {
        try {
            $data = json_decode($clipboardData, true);

            if (! $data || ! isset($data['type'])) {
                return;
            }

            // Handle Row paste
            if ($data['type'] === 'RowBlock') {
                // If pasting through a block, find its parent row
                if (! $targetRowId && $targetBlockId) {
                    foreach ($this->rows as $rowId => $row) {
                        if (isset($row['blocks'][$targetBlockId])) {
                            $targetRowId = $rowId;
                            break;
                        }
                    }
                }

                // Call addRow directly using the same pattern as the method
                $afterRowId = null;
                $beforeRowId = null;

                if ($targetRowId) {
                    if ($position === 'after') {
                        $afterRowId = $targetRowId;
                    } else { // position === 'before'
                        $beforeRowId = $targetRowId;
                    }
                }

                // Create new row ID
                $rowId = uniqid();

                $blocks = [];

                foreach ($data['blocks'] as $blockId => $block) {
                    $blocks[uniqid()] = $block;
                }

                // Create the row with data from clipboard
                $row = [
                    'blocks' => $blocks,
                    'properties' => $data['properties'] ?? [],
                ];

                // Use exact same logic as addRow method
                if ($afterRowId) {
                    $afterRowIndex = array_search($afterRowId, array_keys($this->rows)) + 1;
                    $this->rows = array_merge(
                        array_slice($this->rows, 0, $afterRowIndex),
                        [$rowId => $row],
                        array_slice($this->rows, $afterRowIndex)
                    );
                } elseif ($beforeRowId) {
                    $beforeRowIndex = array_search($beforeRowId, array_keys($this->rows));
                    $this->rows = array_merge(
                        array_slice($this->rows, 0, $beforeRowIndex),
                        [$rowId => $row],
                        array_slice($this->rows, $beforeRowIndex)
                    );
                } else {
                    // If no target position, add to the end
                    $this->rows[$rowId] = $row;
                }

                // Dispatch event to notify about the new row
                $this->dispatch(
                    'row-added',
                    rowId: $rowId,
                    properties: $row['properties']
                );

                $this->dispatch(
                    'notify',
                    message: 'Row pasted successfully',
                    type: 'success'
                );
            }
            // Handle Block paste
            elseif ($data['type'] === 'Block') {
                $blockId = uniqid();
                $block = [
                    'alias' => $data['blockAlias'],
                    'properties' => $data['properties'] ?? [],
                ];

                // Find the row of the target block
                $parentRowId = null;
                foreach ($this->rows as $rowId => $row) {
                    if (isset($row['blocks'][$targetBlockId])) {
                        $parentRowId = $rowId;
                        break;
                    }
                }

                if (! $parentRowId && $targetRowId) {
                    $parentRowId = $targetRowId;
                }

                if ($parentRowId) {
                    if ($targetBlockId) {
                        // Create positioning parameters based on position value
                        $beforeBlockId = null;
                        $afterBlockId = null;

                        if ($position === 'before') {
                            $beforeBlockId = $targetBlockId;
                        } else { // position === 'after'
                            $afterBlockId = $targetBlockId;
                        }

                        // Add the block to the end initially
                        $this->rows[$parentRowId]['blocks'][$blockId] = $block;

                        // Dispatch to row-block component with position parameters
                        $this->dispatch(
                            'block-added',
                            rowId: $parentRowId,
                            blockId: $blockId,
                            blockAlias: $data['blockAlias'],
                            properties: $block['properties'],
                            beforeBlockId: $beforeBlockId,
                            afterBlockId: $afterBlockId
                        );
                    } else {
                        // If no target block, add to the end of row
                        $this->rows[$parentRowId]['blocks'][$blockId] = $block;

                        // Dispatch to row-block component
                        $this->dispatch(
                            'block-added',
                            rowId: $parentRowId,
                            blockId: $blockId,
                            blockAlias: $data['blockAlias'],
                            properties: $block['properties']
                        );
                    }

                    $this->dispatch(
                        'notify',
                        message: 'Block pasted successfully',
                        type: 'success'
                    );
                }
            }
        } catch (\Exception $e) {
            report($e);
            $this->dispatch(
                'notify',
                message: 'Failed to paste: '.$e->getMessage(),
                type: 'error'
            );
        }
    }

    public function render()
    {
        // Format blocks for the modal
        $formattedBlocks = collect($this->availableBlocks)->map(function ($block) {
            $iconComponent = $block['icon'] ?? 'heroicon-o-cube';

            return [
                'alias' => $block['alias'],
                'label' => $block['label'],
                'blockPageName' => $block['blockPageName'] ?? null,
                'icon' => $iconComponent,
            ];
        })->values()->toArray();

        // Apply filter if needed
        if ($this->blockFilter) {
            $filter = strtolower($this->blockFilter);
            $formattedBlocks = collect($formattedBlocks)->filter(function ($block) use ($filter) {
                return str_contains(strtolower($block['label']), $filter) ||
                    str_contains(strtolower($block['alias']), $filter);
            })->values()->toArray();
        }

        // Get all blocks in the page
        $allPageBlocks = $this->getAllPageBlocks();

        // Group blocks by row for the tree view
        $groupedPageBlocks = $this->getGroupedPageBlocks();

        return view('page-builder::livewire.builder.page-editor', [
            'formattedBlocks' => $formattedBlocks,
            'allPageBlocks' => $allPageBlocks,
            'groupedPageBlocks' => $groupedPageBlocks,
        ])->layout('page-builder::layouts.app');
    }

    public function getAllPageBlocks()
    {
        $blocks = [];
        foreach ($this->rows as $rowId => $row) {
            if (isset($row['blocks'])) {
                $this->collectBlocksRecursively($row['blocks'], $rowId, $blocks);
            }
        }

        return $blocks;
    }

    private function collectBlocksRecursively($blocksList, $parentRowId, &$blocks)
    {
        foreach ($blocksList as $blockId => $block) {
            $blockClass = app(PageBuilderService::class)->getClassNameFromAlias($block['alias']);
            if (! $blockClass) {
                continue;
            }

            $blockInstance = app($blockClass);
            $label = $blockInstance->getPageBuilderLabel();
            $icon = $blockInstance->getPageBuilderIcon() ?? 'heroicon-o-cube';

            $blocks[] = [
                'id' => $blockId,
                'rowId' => $parentRowId,
                'alias' => $block['alias'],
                'label' => $label,
                'icon' => $icon,
            ];

            // If this block is a RowBlock (nested row), recursively collect its blocks
            if ($blockClass === \Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock::class && isset($block['blocks'])) {
                $this->collectBlocksRecursively($block['blocks'], $blockId, $blocks);
            }
        }
    }

    /**
     * Check if current page is a block
     */
    public function isCurrentPageBlock(): bool
    {
        $pages = config('page-builder.pages', []);

        foreach ($pages as $pageKey => $pageInfo) {
            if (is_int($pageKey)) {
                continue;
            }
            if ($pageKey === $this->pageKey) {
                if (is_array($pageInfo) && isset($pageInfo['is_block'])) {
                    return $pageInfo['is_block'];
                }
            }
        }

        return false;
    }

    /**
     * Get the current page label for display
     */
    public function getCurrentPageLabel(): string
    {
        $pages = config('page-builder.pages', []);

        foreach ($pages as $pageKey => $pageInfo) {
            if (is_int($pageKey)) {
                continue;
            }
            if ($pageKey === $this->pageKey) {
                if (isset($pageInfo['label'])) {
                    return __($pageInfo['label']);
                }
            }
        }

        return __(Str::headline($this->pageKey));
    }

    /**
     * Get pages with their component status
     */
    public function getPagesWithStatus(): array
    {
        $pages = config('page-builder.pages', []);
        $pagesWithStatus = [];

        foreach ($pages as $pageKey => $pageInfo) {
            if (is_int($pageKey)) {
                continue;
            }

            $pageName = $pageKey;
            $pageLabel = null;

            if (is_array($pageInfo)) {
                if (isset($pageInfo['label'])) {
                    $pageLabel = $pageInfo['label'];
                } else {
                    $pageLabel = Str::headline($pageKey);
                }
            } else {
                $pageName = $pageInfo;
                $pageLabel = Str::headline($pageInfo);
            }

            // Check if this page has components
            $hasComponents = false;
            if ($this->themeId) {
                $page = BuilderPage::where('key', $pageName)
                    ->where('theme_id', $this->themeId)
                    ->first();
            } else {
                $page = BuilderPage::where('key', $pageName)
                    ->whereNull('theme_id')
                    ->first();
            }

            if ($page && $page->components) {
                $components = $page->components;
                $hasComponents = (bool) $components;
            }

            // Check if this page is marked as a block
            $isBlock = false;
            if (is_array($pageInfo) && isset($pageInfo['is_block'])) {
                $isBlock = $pageInfo['is_block'];
            }

            $pagesWithStatus[] = [
                'key' => $pageName,
                'label' => __($pageLabel),
                'hasComponents' => $hasComponents,
                'isCurrentPage' => $pageName === $this->pageKey,
                'isBlock' => $isBlock,
            ];
        }

        return $pagesWithStatus;
    }

    /**
     * Copy components from another page
     */
    public function copyComponentsFromPage(string $sourcePageKey): void
    {
        // Don't allow copying from the current page
        if ($sourcePageKey === $this->pageKey) {
            return;
        }

        // Find the source page
        $sourcePage = null;
        if ($this->themeId) {
            $sourcePage = BuilderPage::where('key', $sourcePageKey)
                ->where('theme_id', $this->themeId)
                ->first();
        } else {
            $sourcePage = BuilderPage::where('key', $sourcePageKey)
                ->whereNull('theme_id')
                ->first();
        }

        if (! $sourcePage || ! $sourcePage->components) {
            return;
        }

        // Copy the components
        $this->rows = $sourcePage->components;

        // Save the current page with copied components
        $this->savePage();

        // Show success message
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('Components copied successfully from :page', ['page' => $this->getPageLabel($sourcePageKey)]),
        ]);
    }

    /**
     * Check if current page has content
     */
    public function currentPageHasContent(): bool
    {
        return ! empty($this->rows);
    }

    /**
     * Get confirmation message for copying
     */
    public function getCopyConfirmationMessage(string $sourcePageKey): string
    {
        $sourcePageLabel = $this->getPageLabel($sourcePageKey);

        return __('Are you sure you want to copy components from ":page"? This will replace all current page content.', ['page' => $sourcePageLabel]);
    }

    /**
     * Prepare copy confirmation data
     */
    public function prepareCopyConfirmation(string $sourcePageKey): array
    {
        $sourcePageLabel = $this->getPageLabel($sourcePageKey);

        return [
            'sourcePageKey' => $sourcePageKey,
            'sourcePageLabel' => $sourcePageLabel,
            'confirmationMessage' => __('Are you sure you want to copy components from ":page"? This will replace all current page content.', ['page' => $sourcePageLabel]),
        ];
    }

    /**
     * Get page label for a specific page key
     */
    private function getPageLabel(string $pageKey): string
    {
        $pages = config('page-builder.pages', []);

        foreach ($pages as $key => $pageInfo) {
            if (is_int($key)) {
                continue;
            }
            if ($key === $pageKey) {
                if (is_array($pageInfo) && isset($pageInfo['label'])) {
                    return __($pageInfo['label']);
                }

                return __(Str::headline($pageKey));
            }
        }

        return __(Str::headline($pageKey));
    }

    public function getGroupedPageBlocks()
    {
        $grouped = [];

        foreach ($this->rows as $rowId => $row) {
            $blocks = $this->getBlocksWithNesting($row['blocks']);

            $grouped[$rowId] = [
                'blocks' => $blocks,
            ];
        }

        return $grouped;
    }

    private function getBlocksWithNesting($blocksList)
    {
        $blocks = [];

        foreach ($blocksList as $blockId => $block) {
            $blockClass = app(PageBuilderService::class)->getClassNameFromAlias($block['alias']);
            if (! $blockClass) {
                continue;
            }

            $blockInstance = app($blockClass);
            $label = $blockInstance->getPageBuilderLabel();
            $icon = $blockInstance->getPageBuilderIcon() ?? 'heroicon-o-cube';

            $blockData = [
                'id' => $blockId,
                'alias' => $block['alias'],
                'label' => $label,
                'icon' => $icon,
            ];

            // If this block is a RowBlock (nested row), add its nested blocks
            if ($blockClass === \Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock::class && isset($block['blocks'])) {
                Log::info('Found nested row with blocks:', [
                    'blockId' => $blockId,
                    'nestedBlocks' => $block['blocks'],
                ]);
                $blockData['nestedBlocks'] = $this->getBlocksWithNesting($block['blocks']);
            }

            $blocks[] = $blockData;
        }

        return $blocks;
    }
}
