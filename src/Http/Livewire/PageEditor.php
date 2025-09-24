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
                    blockPageName: $blockPageName,
                    beforeBlockId: $this->beforeBlockId,
                    afterBlockId: $this->afterBlockId
                );
            }
            $this->closeBlockModal();
        }
    }

    #[On('sync-nested-row-data')]
    public function syncNestedRowData($nestedRowId, $blocks)
    {
        Log::info('PageEditor syncNestedRowData called', [
            'nestedRowId' => $nestedRowId,
            'blocksCount' => count($blocks),
            'blocksData' => $blocks,
        ]);

        // Find the nested row in the structure and update its blocks
        $result = $this->updateNestedRowBlocks($this->rows, $nestedRowId, $blocks);

        Log::info('PageEditor syncNestedRowData result', [
            'nestedRowId' => $nestedRowId,
            'updateSuccess' => $result,
            'currentRowsStructure' => $this->rows,
        ]);
    }

    private function updateNestedRowBlocks(&$structure, $nestedRowId, $blocks, $depth = 0)
    {
        $indent = str_repeat('  ', $depth);
        Log::info("{$indent}updateNestedRowBlocks called", [
            'targetNestedRowId' => $nestedRowId,
            'depth' => $depth,
            'structureKeys' => array_keys($structure),
            'blocksToSet' => $blocks,
        ]);

        foreach ($structure as $rowId => $row) {
            Log::info("{$indent}Checking row: {$rowId}");

            // Check if this rowId is the target we want to update
            if ($rowId === $nestedRowId) {
                Log::info("{$indent}FOUND TARGET ROW AS KEY! Updating blocks for row {$nestedRowId}");
                $structure[$rowId]['blocks'] = $blocks;
                Log::info("{$indent}Updated blocks for target row {$nestedRowId}", [
                    'newBlocksCount' => count($blocks),
                    'newBlocks' => $blocks,
                ]);

                return true;
            }

            if (isset($row['blocks'])) {
                Log::info("{$indent}Row {$rowId} has blocks, checking ".count($row['blocks']).' blocks');
                foreach ($row['blocks'] as $blockId => $block) {
                    Log::info("{$indent}  Checking block: {$blockId}");

                    // Check if this block is the nested row we're looking for
                    if ($blockId === $nestedRowId) {
                        Log::info("{$indent}  FOUND TARGET ROW AS BLOCK! Updating blocks for nested row {$nestedRowId}");
                        $structure[$rowId]['blocks'][$blockId]['blocks'] = $blocks;

                        return true;
                    }

                    // If this block has nested blocks, recursively search
                    if (isset($block['blocks'])) {
                        Log::info("{$indent}  Block {$blockId} has nested blocks, recursing");
                        $nestedBlocks = &$structure[$rowId]['blocks'][$blockId]['blocks'];
                        if ($this->updateNestedRowBlocks($nestedBlocks, $nestedRowId, $blocks, $depth + 1)) {
                            return true;
                        }
                    }
                }
            }
        }

        Log::info("{$indent}updateNestedRowBlocks: Target {$nestedRowId} not found at depth {$depth}");

        return false;
    }

    private function updateNestedRowProperty(&$structure, $nestedRowId, $propertyName, $value, $depth = 0)
    {
        $indent = str_repeat('  ', $depth);
        Log::info("{$indent}updateNestedRowProperty called", [
            'targetNestedRowId' => $nestedRowId,
            'propertyName' => $propertyName,
            'value' => $value,
            'depth' => $depth,
            'structureKeys' => array_keys($structure),
        ]);

        foreach ($structure as $rowId => $row) {
            Log::info("{$indent}Checking row: {$rowId}");

            // Check if this rowId is the target we want to update
            if ($rowId === $nestedRowId) {
                Log::info("{$indent}FOUND TARGET ROW AS KEY! Updating property for row {$nestedRowId}");
                $structure[$rowId]['properties'][$propertyName] = $value;
                Log::info("{$indent}Updated property for target row {$nestedRowId}", [
                    'propertyName' => $propertyName,
                    'newValue' => $value,
                ]);

                return true;
            }

            if (isset($row['blocks'])) {
                Log::info("{$indent}Row {$rowId} has blocks, checking ".count($row['blocks']).' blocks');
                foreach ($row['blocks'] as $blockId => $block) {
                    Log::info("{$indent}  Checking block: {$blockId}");

                    // Check if this block is the nested row we're looking for
                    if ($blockId === $nestedRowId) {
                        Log::info("{$indent}  FOUND TARGET ROW AS BLOCK! Updating property for nested row {$nestedRowId}");
                        $structure[$rowId]['blocks'][$blockId]['properties'][$propertyName] = $value;
                        Log::info("{$indent}  Updated property for nested row {$nestedRowId}", [
                            'parentRowId' => $rowId,
                            'propertyName' => $propertyName,
                            'newValue' => $value,
                        ]);

                        return true;
                    }

                    // If this block has nested blocks, recursively search
                    if (isset($block['blocks'])) {
                        Log::info("{$indent}  Block {$blockId} has nested blocks, recursing");
                        $nestedBlocks = &$structure[$rowId]['blocks'][$blockId]['blocks'];
                        if ($this->updateNestedRowProperty($nestedBlocks, $nestedRowId, $propertyName, $value, $depth + 1)) {
                            return true;
                        }
                    }
                }
            }
        }

        Log::info("{$indent}updateNestedRowProperty: Target {$nestedRowId} not found at depth {$depth}");

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
                // Check if it's a nested row - use recursive search for deeply nested rows
                $nestedRowFound = $this->updateNestedRowProperty($this->rows, $rowId, $propertyName, $value);

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
        // Check if auto-save is needed after block movement
        Log::info('Block movement completed, checking if auto-save is needed');
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
        Log::info('PageEditor::moveRowUp called', [
            'rowId' => $rowId,
            'totalRows' => count($this->rows),
            'currentOrder' => array_keys($this->rows),
        ]);

        $rowIds = array_keys($this->rows);
        $currentIndex = array_search($rowId, $rowIds);

        Log::info('Row movement analysis', [
            'currentIndex' => $currentIndex,
            'canMoveUp' => $currentIndex > 0,
        ]);

        if ($currentIndex > 0) {
            $newOrder = $rowIds;
            $temp = $newOrder[$currentIndex - 1];
            $newOrder[$currentIndex - 1] = $newOrder[$currentIndex];
            $newOrder[$currentIndex] = $temp;

            $this->rows = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $this->rows[$id]])->toArray();

            Log::info('Row moved up successfully', [
                'rowId' => $rowId,
                'newOrder' => array_keys($this->rows),
            ]);
        } else {
            Log::info('Row cannot be moved up - already at top or not found', ['rowId' => $rowId]);
        }
    }

    #[On('moveRowDown')]
    public function moveRowDown($rowId)
    {
        Log::info('PageEditor::moveRowDown called', [
            'rowId' => $rowId,
            'totalRows' => count($this->rows),
            'currentOrder' => array_keys($this->rows),
        ]);

        $rowIds = array_keys($this->rows);
        $currentIndex = array_search($rowId, $rowIds);

        Log::info('Row movement analysis', [
            'currentIndex' => $currentIndex,
            'canMoveDown' => $currentIndex < count($this->rows) - 1,
        ]);

        if ($currentIndex < count($this->rows) - 1) {
            $newOrder = $rowIds;
            $temp = $newOrder[$currentIndex + 1];
            $newOrder[$currentIndex + 1] = $newOrder[$currentIndex];
            $newOrder[$currentIndex] = $temp;

            $this->rows = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $this->rows[$id]])->toArray();

            Log::info('Row moved down successfully', [
                'rowId' => $rowId,
                'newOrder' => array_keys($this->rows),
            ]);
        } else {
            Log::info('Row cannot be moved down - already at bottom or not found', ['rowId' => $rowId]);
        }
    }

    #[On('moveBlockUp')]
    public function moveBlockUp($blockId)
    {
        Log::info('PageEditor::moveBlockUp called', [
            'blockId' => $blockId,
            'totalRows' => count($this->rows),
        ]);

        // Only handle top-level blocks, let RowBlock handle nested blocks
        foreach ($this->rows as $rowId => $row) {
            if (isset($row['blocks'][$blockId])) {
                Log::info('Block found in top-level row - PageEditor will handle movement', [
                    'blockId' => $blockId,
                    'rowId' => $rowId,
                    'blocksInRow' => array_keys($row['blocks']),
                ]);

                $blockIds = array_keys($row['blocks']);
                $currentIndex = array_search($blockId, $blockIds);

                Log::info('Block movement analysis', [
                    'currentIndex' => $currentIndex,
                    'canMoveUp' => $currentIndex > 0,
                ]);

                if ($currentIndex > 0) {
                    $newOrder = $blockIds;
                    $temp = $newOrder[$currentIndex - 1];
                    $newOrder[$currentIndex - 1] = $newOrder[$currentIndex];
                    $newOrder[$currentIndex] = $temp;

                    $this->rows[$rowId]['blocks'] = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $row['blocks'][$id]])->toArray();

                    Log::info('Block moved up successfully in top-level row', [
                        'blockId' => $blockId,
                        'rowId' => $rowId,
                        'newOrder' => array_keys($this->rows[$rowId]['blocks']),
                    ]);
                } else {
                    Log::info('Block cannot be moved up - already at top', [
                        'blockId' => $blockId,
                        'rowId' => $rowId,
                    ]);
                }

                return; // Exit early - we found and handled the block
            }
        }

        Log::info('Block not found in top-level rows - RowBlock will handle nested movement', [
            'blockId' => $blockId,
        ]);
    }

    #[On('moveBlockDown')]
    public function moveBlockDown($blockId)
    {
        Log::info('PageEditor::moveBlockDown called', [
            'blockId' => $blockId,
            'totalRows' => count($this->rows),
        ]);

        // Only handle top-level blocks, let RowBlock handle nested blocks
        foreach ($this->rows as $rowId => $row) {
            if (isset($row['blocks'][$blockId])) {
                Log::info('Block found in top-level row - PageEditor will handle movement', [
                    'blockId' => $blockId,
                    'rowId' => $rowId,
                    'blocksInRow' => array_keys($row['blocks']),
                ]);

                $blockIds = array_keys($row['blocks']);
                $currentIndex = array_search($blockId, $blockIds);

                Log::info('Block movement analysis', [
                    'currentIndex' => $currentIndex,
                    'totalBlocks' => count($row['blocks']),
                    'canMoveDown' => $currentIndex < count($row['blocks']) - 1,
                ]);

                if ($currentIndex < count($row['blocks']) - 1) {
                    $newOrder = $blockIds;
                    $temp = $newOrder[$currentIndex + 1];
                    $newOrder[$currentIndex + 1] = $newOrder[$currentIndex];
                    $newOrder[$currentIndex] = $temp;

                    $this->rows[$rowId]['blocks'] = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $row['blocks'][$id]])->toArray();

                    Log::info('Block moved down successfully in top-level row', [
                        'blockId' => $blockId,
                        'rowId' => $rowId,
                        'newOrder' => array_keys($this->rows[$rowId]['blocks']),
                    ]);
                } else {
                    Log::info('Block cannot be moved down - already at bottom', [
                        'blockId' => $blockId,
                        'rowId' => $rowId,
                    ]);
                }

                return; // Exit early - we found and handled the block
            }
        }

        Log::info('Block not found in top-level rows - RowBlock will handle nested movement', [
            'blockId' => $blockId,
        ]);
    }

    #[On('syncBlockOrder')]
    public function syncBlockOrder($data)
    {
        $rowId = $data['rowId'];
        $blockOrder = $data['blockOrder'];

        Log::info('PageEditor::syncBlockOrder called', [
            'rowId' => $rowId,
            'newBlockOrder' => $blockOrder,
        ]);

        // Find the row and update its block order recursively
        $this->updateBlockOrderInStructure($this->rows, $rowId, $blockOrder);
    }

    private function updateBlockOrderInStructure(&$structure, $targetRowId, $blockOrder)
    {
        foreach ($structure as $rowId => &$row) {
            // Check if this is the target row
            if ($rowId === $targetRowId && isset($row['blocks'])) {
                $currentBlocks = $row['blocks'];
                $reorderedBlocks = [];

                // Reorder blocks according to new order
                foreach ($blockOrder as $blockId) {
                    if (isset($currentBlocks[$blockId])) {
                        $reorderedBlocks[$blockId] = $currentBlocks[$blockId];
                    }
                }

                $row['blocks'] = $reorderedBlocks;

                Log::info('PageEditor block order synced successfully', [
                    'rowId' => $targetRowId,
                    'syncedOrder' => array_keys($row['blocks']),
                ]);

                return true;
            }

            // Recursively search in nested blocks
            if (isset($row['blocks'])) {
                foreach ($row['blocks'] as $blockId => &$block) {
                    if (isset($block['blocks'])) {
                        $result = $this->updateBlockOrderInStructure($row['blocks'][$blockId]['blocks'], $targetRowId, $blockOrder);
                        if ($result) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    #[On('deleteRow')]
    public function deleteRow($rowId)
    {
        Log::info('PageEditor::deleteRow called', [
            'rowId' => $rowId,
            'totalTopLevelRows' => count($this->rows),
            'topLevelRowIds' => array_keys($this->rows),
        ]);

        // First check if it's a top-level row
        if (isset($this->rows[$rowId])) {
            unset($this->rows[$rowId]);
            Log::info('Top-level row deleted successfully', ['rowId' => $rowId]);

            return;
        }

        // If not found as top-level row, search recursively for nested rows
        Log::info('Searching for nested row', [
            'targetRowId' => $rowId,
            'searchingInStructure' => array_keys($this->rows),
        ]);

        $result = $this->deleteNestedRowWithParent($this->rows, $rowId);
        if ($result) {
            Log::info('Nested row deleted successfully', [
                'deletedNestedRowId' => $rowId,
                'parentRowId' => $result['parentRowId'],
            ]);

            // Dispatch the event to notify components
            $this->dispatch('nested-row-deleted',
                parentRowId: $result['parentRowId'],
                deletedRowId: $rowId,
                updatedBlocks: $result['updatedBlocks']
            );

            return;
        }

        Log::warning('Row not found for deletion', ['rowId' => $rowId]);
    }

    /**
     * Recursively search and delete a nested row with parent tracking
     */
    private function deleteNestedRowWithParent(&$structure, $rowId, $parentRowId = null, $depth = 0)
    {
        $indent = str_repeat('  ', $depth);
        Log::info("{$indent}deleteNestedRowWithParent: Searching at depth {$depth}", [
            'targetRowId' => $rowId,
            'currentLevelKeys' => array_keys($structure),
            'parentRowId' => $parentRowId,
            'depth' => $depth,
        ]);

        foreach ($structure as $currentRowId => &$row) {
            Log::info("{$indent}Checking row/block: {$currentRowId}", [
                'hasBlocks' => isset($row['blocks']),
                'blocksCount' => isset($row['blocks']) ? count($row['blocks']) : 0,
            ]);

            // Check if this currentRowId is the target we want to delete
            if ($currentRowId === $rowId) {
                Log::info("{$indent}FOUND TARGET ROW AS KEY! Deleting row {$currentRowId} (parent: {$parentRowId})");
                unset($structure[$currentRowId]);

                return [
                    'parentRowId' => $parentRowId,
                    'updatedBlocks' => $structure,
                ];
            }

            if (isset($row['blocks'])) {
                Log::info("{$indent}  Row has blocks, iterating through ".count($row['blocks']).' blocks');
                foreach ($row['blocks'] as $blockId => &$block) {
                    Log::info("{$indent}  Checking block: {$blockId}", [
                        'isTargetRow' => $blockId === $rowId,
                        'hasNestedBlocks' => isset($block['blocks']),
                        'alias' => $block['alias'] ?? 'unknown',
                    ]);

                    // Check if this block is the row we want to delete
                    Log::info("{$indent}  Comparing blockId '{$blockId}' with target '{$rowId}' - Match: ".($blockId === $rowId ? 'YES' : 'NO'));
                    if ($blockId === $rowId) {
                        Log::info("{$indent}  FOUND TARGET ROW! Deleting block {$blockId} from parent {$currentRowId}");
                        unset($structure[$currentRowId]['blocks'][$blockId]);

                        return [
                            'parentRowId' => $currentRowId,
                            'updatedBlocks' => $structure[$currentRowId]['blocks'],
                        ];
                    }

                    // If this block has nested blocks, search recursively
                    if (isset($block['blocks'])) {
                        Log::info("{$indent}  Recursing into block {$blockId} with ".count($block['blocks']).' nested blocks');
                        Log::info("{$indent}  Recursive structure keys: ".json_encode(array_keys($block['blocks'])));
                        $result = $this->deleteNestedRowWithParent($block['blocks'], $rowId, $blockId, $depth + 1);
                        if ($result) {
                            return $result;
                        }
                    }
                }
            }
        }

        Log::info("{$indent}deleteNestedRowWithParent: Target not found at depth {$depth}");

        return false;
    }

    /**
     * Recursively search and delete a nested row
     */
    private function deleteNestedRow(&$structure, $rowId, $depth = 0)
    {
        $indent = str_repeat('  ', $depth);
        Log::info("{$indent}deleteNestedRow: Searching at depth {$depth}", [
            'targetRowId' => $rowId,
            'currentLevelKeys' => array_keys($structure),
            'depth' => $depth,
        ]);

        foreach ($structure as $currentRowId => &$row) {
            Log::info("{$indent}Checking row/block: {$currentRowId}", [
                'hasBlocks' => isset($row['blocks']),
                'blocksCount' => isset($row['blocks']) ? count($row['blocks']) : 0,
            ]);

            // Check if this currentRowId is the target we want to delete
            if ($currentRowId === $rowId) {
                Log::info("{$indent}FOUND TARGET ROW! Deleting row {$currentRowId}");
                unset($structure[$currentRowId]);

                // For this case, we need to find the parent to dispatch the event
                // This should be handled by the calling context
                Log::info('Target row deleted successfully from structure', [
                    'deletedRowId' => $rowId,
                ]);

                return true;
            }

            if (isset($row['blocks'])) {
                Log::info("{$indent}  Row has blocks, iterating through ".count($row['blocks']).' blocks');
                foreach ($row['blocks'] as $blockId => &$block) {
                    Log::info("{$indent}  Checking block: {$blockId}", [
                        'isTargetRow' => $blockId === $rowId,
                        'hasNestedBlocks' => isset($block['blocks']),
                        'alias' => $block['alias'] ?? 'unknown',
                    ]);

                    // Check if this block is the row we want to delete
                    Log::info("{$indent}  Comparing blockId '{$blockId}' with target '{$rowId}' - Match: ".($blockId === $rowId ? 'YES' : 'NO'));
                    if ($blockId === $rowId) {
                        Log::info("{$indent}  FOUND TARGET ROW! Deleting block {$blockId} from parent {$currentRowId}");
                        unset($structure[$currentRowId]['blocks'][$blockId]);

                        // Notify the parent RowBlock component to update its blocks
                        $this->dispatch('nested-row-deleted',
                            parentRowId: $currentRowId,
                            deletedRowId: $rowId,
                            updatedBlocks: $structure[$currentRowId]['blocks']
                        );

                        Log::info('PageEditor nested row deletion completed - dispatched nested-row-deleted event', [
                            'parentRowId' => $currentRowId,
                            'deletedRowId' => $rowId,
                            'remainingBlocksCount' => count($structure[$currentRowId]['blocks']),
                        ]);

                        return true;
                    }

                    // If this block has nested blocks, search recursively
                    if (isset($block['blocks'])) {
                        Log::info("{$indent}  Recursing into block {$blockId} with ".count($block['blocks']).' nested blocks');
                        if ($this->deleteNestedRow($block['blocks'], $rowId, $depth + 1)) {
                            return true;
                        }
                    }
                }
            }
        }

        Log::info("{$indent}deleteNestedRow: Target not found at depth {$depth}");

        return false;
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
