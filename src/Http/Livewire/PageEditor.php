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

    public ?string $replaceBlockId = null;

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

            // Validate that the theme exists
            if (! $this->currentTheme) {
                // Theme ID was provided but doesn't exist in the database
                abort(404, 'Theme not found. The theme with ID '.$this->themeId.' does not exist.');
            }
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
    public function openBlockModal($rowId, $beforeBlockId = null, $afterBlockId = null, $replaceBlockId = null)
    {
        $this->showBlockModal = true;
        $this->blockFilter = '';
        $this->modalRowId = $rowId;
        $this->beforeBlockId = $beforeBlockId;
        $this->afterBlockId = $afterBlockId;
        $this->replaceBlockId = $replaceBlockId;
    }

    public function closeBlockModal()
    {
        $this->showBlockModal = false;
        $this->modalRowId = null;
        $this->beforeBlockId = null;
        $this->afterBlockId = null;
        $this->replaceBlockId = null;
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
            // ALL rows are rendered as RowBlock components, so we ALWAYS dispatch the event
            // This ensures the RowBlock component updates its own $blocks state
            $this->dispatch('add-block-to-nested-row',
                rowId: $this->modalRowId,
                blockAlias: $blockAlias,
                blockPageName: $blockPageName,
                beforeBlockId: $this->beforeBlockId,
                afterBlockId: $this->afterBlockId,
                replaceBlockId: $this->replaceBlockId
            );
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

    #[On('duplicateBlock')]
    public function duplicateBlock($data)
    {
        $blockId = $data['blockId'] ?? null;
        $blockAlias = $data['blockAlias'] ?? null;
        $properties = $data['properties'] ?? [];
        $blocks = $data['blocks'] ?? null;
        $rowId = $data['rowId'] ?? null;

        if (! $blockId || ! $blockAlias) {
            Log::warning('duplicateBlock: Missing required data', [
                'blockId' => $blockId,
                'blockAlias' => $blockAlias,
            ]);

            return;
        }

        Log::info('PageEditor::duplicateBlock called', [
            'blockId' => $blockId,
            'blockAlias' => $blockAlias,
            'rowId' => $rowId,
            'hasBlocks' => ! empty($blocks),
        ]);

        // Try to find the block in top-level rows first
        $foundRowId = null;
        $foundBlock = null;

        if ($rowId && isset($this->rows[$rowId]['blocks'][$blockId])) {
            $foundRowId = $rowId;
            $foundBlock = $this->rows[$rowId]['blocks'][$blockId];
            Log::info('✅ Block found in top-level row', ['rowId' => $rowId]);
        } else {
            // Search top-level rows
            foreach ($this->rows as $rId => $row) {
                if (isset($row['blocks'][$blockId])) {
                    $foundRowId = $rId;
                    $foundBlock = $row['blocks'][$blockId];
                    Log::info('✅ Block found in top-level search', ['rowId' => $rId]);
                    break;
                }
            }

            // If not found in top-level, search nested rows
            if (! $foundRowId) {
                Log::info('🔍 Searching in nested rows for block', ['blockId' => $blockId]);
                $foundRowId = $this->findBlockInNestedRows($this->rows, $blockId);

                if ($foundRowId) {
                    Log::info('✅ Block found in nested row', ['nestedRowId' => $foundRowId]);
                    // Get the block data from the nested row
                    $foundBlock = $this->getBlockFromNestedRow($this->rows, $foundRowId, $blockId);
                }
            }
        }

        if (! $foundRowId || ! $foundBlock) {
            Log::warning('❌ duplicateBlock: Block not found anywhere', [
                'blockId' => $blockId,
                'rowId' => $rowId,
            ]);

            $this->dispatch(
                'notify',
                message: __('Failed to duplicate block: Block not found'),
                type: 'error'
            );

            return;
        }

        // Create new block ID
        $newBlockId = uniqid();

        // Clone the block data
        $newBlock = [
            'alias' => $foundBlock['alias'],
            'properties' => $foundBlock['properties'] ?? [],
        ];

        // If this block has nested blocks (RowBlock), clone them with new IDs
        if (! empty($foundBlock['blocks'])) {
            $newBlock['blocks'] = $this->regenerateBlockIds($foundBlock['blocks']);
        }

        Log::info('Block duplicated - preparing to dispatch events', [
            'originalBlockId' => $blockId,
            'newBlockId' => $newBlockId,
            'rowId' => $foundRowId,
        ]);

        Log::info('🔄 Dispatching block-added event', [
            'rowId' => $foundRowId,
            'blockId' => $newBlockId,
            'blockAlias' => $newBlock['alias'],
            'afterBlockId' => $blockId,
            'hasProperties' => ! empty($newBlock['properties']),
            'propertiesCount' => count($newBlock['properties']),
        ]);

        Log::info('🔍 Current rows structure', [
            'rowIds' => array_keys($this->rows),
            'targetRowExists' => isset($this->rows[$foundRowId]),
            'targetRowBlockCount' => isset($this->rows[$foundRowId]) ? count($this->rows[$foundRowId]['blocks']) : 0,
        ]);

        // DON'T modify $this->rows here - let RowBlock handle it via block-added event
        // RowBlock will add the block to its local state and sync back via sync-nested-row-data

        // Dispatch 'block-added' event - RowBlock will handle adding it to its blocks array
        Log::info('⚡ About to dispatch block-added event...');
        $this->dispatch(
            'block-added',
            rowId: $foundRowId,
            blockId: $newBlockId,
            blockAlias: $newBlock['alias'],
            properties: $newBlock['properties'],
            beforeBlockId: null,
            afterBlockId: $blockId  // Insert after the original block
        );
        Log::info('⚡ Dispatch completed');

        Log::info('📢 Dispatching block-duplicated event', [
            'blockId' => $newBlockId,
        ]);

        // Dispatch custom 'block-duplicated' event to handle scroll and selection with proper timing
        $this->dispatch(
            'block-duplicated',
            blockId: $newBlockId
        );

        Log::info('🔔 Dispatching notify event');

        // Success notification
        $this->dispatch(
            'notify',
            message: __('Block duplicated successfully'),
            type: 'success'
        );

        Log::info('✅ All duplicate events dispatched successfully');
    }

    /**
     * Duplicate a row (clone and place after current row with all its blocks).
     */
    #[On('duplicateRow')]
    public function duplicateRow($data)
    {
        $rowId = $data['rowId'] ?? null;
        $properties = $data['properties'] ?? [];
        $blocks = $data['blocks'] ?? [];
        $isNested = $data['isNested'] ?? false;

        if (! $rowId) {
            Log::warning('duplicateRow: Missing required rowId', ['data' => $data]);
            $this->dispatch(
                'notify',
                message: __('Failed to duplicate row: Missing row ID'),
                type: 'error'
            );

            return;
        }

        Log::info('PageEditor::duplicateRow called', [
            'rowId' => $rowId,
            'isNested' => $isNested,
            'blocksCount' => count($blocks),
            'hasProperties' => ! empty($properties),
        ]);

        // Check if this is a top-level row or nested row
        if (isset($this->rows[$rowId])) {
            // This is a top-level row
            Log::info('✅ Duplicating top-level row', ['rowId' => $rowId]);

            $originalRow = $this->rows[$rowId];

            // Generate new row ID
            $newRowId = uniqid();

            // Clone the row with regenerated block IDs
            $newRow = [
                'properties' => $originalRow['properties'] ?? [],
                'blocks' => $this->regenerateBlockIds($originalRow['blocks'] ?? []),
            ];

            Log::info('Created new row clone', [
                'originalRowId' => $rowId,
                'newRowId' => $newRowId,
                'newBlocksCount' => count($newRow['blocks']),
            ]);

            // Find position of original row and insert after it
            $rowKeys = array_keys($this->rows);
            $position = array_search($rowId, $rowKeys);

            if ($position !== false) {
                $insertPosition = $position + 1;

                // Insert the new row after the original
                $newRows = array_merge(
                    array_slice($this->rows, 0, $insertPosition, true),
                    [$newRowId => $newRow],
                    array_slice($this->rows, $insertPosition, null, true)
                );

                $this->rows = $newRows;

                Log::info('✅ Top-level row duplicated successfully', [
                    'originalRowId' => $rowId,
                    'newRowId' => $newRowId,
                    'insertedAt' => $insertPosition,
                    'totalRows' => count($this->rows),
                ]);

                // Dispatch event for scroll and selection
                $this->dispatch('row-duplicated', rowId: $newRowId);

                $this->dispatch(
                    'notify',
                    message: __('Row duplicated successfully'),
                    type: 'success'
                );

                return;
            }
        } else {
            // This is a nested row (RowBlock) - find its parent
            Log::info('🔍 Searching for nested row parent', ['nestedRowId' => $rowId]);

            $parentRowId = null;
            foreach ($this->rows as $rId => $row) {
                if (isset($row['blocks'][$rowId])) {
                    $parentRowId = $rId;
                    break;
                }
            }

            if ($parentRowId) {
                Log::info('✅ Found parent row for nested row', [
                    'nestedRowId' => $rowId,
                    'parentRowId' => $parentRowId,
                ]);

                // Generate new block/row ID for the duplicate
                $newBlockId = uniqid();

                // Clone the nested row block with regenerated IDs
                $originalNestedRow = $this->rows[$parentRowId]['blocks'][$rowId];
                $newNestedRow = [
                    'alias' => $originalNestedRow['alias'] ?? 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                    'properties' => $originalNestedRow['properties'] ?? [],
                    'blocks' => $this->regenerateBlockIds($originalNestedRow['blocks'] ?? []),
                ];

                Log::info('Created nested row clone', [
                    'originalNestedRowId' => $rowId,
                    'newBlockId' => $newBlockId,
                    'newNestedBlocksCount' => count($newNestedRow['blocks']),
                ]);

                // DON'T modify $this->rows directly - let RowBlock handle it via block-added event
                // Dispatch 'block-added' event - parent RowBlock will handle adding it to its blocks array
                Log::info('⚡ Dispatching block-added event for nested row duplication');
                $this->dispatch(
                    'block-added',
                    rowId: $parentRowId,
                    blockId: $newBlockId,
                    blockAlias: $newNestedRow['alias'],
                    properties: $newNestedRow['properties'],
                    blocks: $newNestedRow['blocks'],
                    beforeBlockId: null,
                    afterBlockId: $rowId  // Insert after the original nested row
                );

                Log::info('📢 Dispatching row-duplicated event for nested row', [
                    'blockId' => $newBlockId,
                ]);

                // Dispatch event for scroll and selection (using blockId since nested rows are blocks)
                $this->dispatch('row-duplicated', rowId: $newBlockId);

                $this->dispatch(
                    'notify',
                    message: __('Nested row duplicated successfully'),
                    type: 'success'
                );

                return;
            }
        }

        // If we reach here, something went wrong
        Log::warning('❌ duplicateRow: Row not found', ['rowId' => $rowId]);
        $this->dispatch(
            'notify',
            message: __('Failed to duplicate row: Row not found'),
            type: 'error'
        );
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
        Log::info('PageEditor::pasteFromClipboard called', [
            'targetRowId' => $targetRowId,
            'targetBlockId' => $targetBlockId,
            'position' => $position,
            'clipboardDataLength' => strlen($clipboardData ?? ''),
        ]);

        try {
            $data = json_decode($clipboardData, true);

            Log::info('Clipboard data decoded', [
                'hasData' => ! empty($data),
                'type' => $data['type'] ?? 'null',
                'dataKeys' => $data ? array_keys($data) : [],
            ]);

            if (! $data || ! isset($data['type'])) {
                Log::warning('Invalid clipboard data - missing data or type');

                return;
            }

            // Handle Row paste
            if ($data['type'] === 'RowBlock') {
                Log::info('Pasting RowBlock', [
                    'targetRowId' => $targetRowId,
                    'targetBlockId' => $targetBlockId,
                    'position' => $position,
                ]);

                // Handle "inside" position - paste as a nested block within the target row
                if ($position === 'inside' && $targetRowId) {
                    Log::info('Pasting RowBlock INSIDE target row', [
                        'targetRowId' => $targetRowId,
                    ]);

                    $blockId = uniqid();

                    // Generate new IDs for nested blocks
                    $blocks = $this->regenerateBlockIds($data['blocks'] ?? []);

                    // Create the nested RowBlock properties
                    $nestedRowProperties = $data['properties'] ?? [];
                    $nestedRowAlias = $data['blockAlias'] ?? 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block';

                    // Check if target row exists as a top-level row OR as a nested row
                    $isTopLevelRow = isset($this->rows[$targetRowId]);

                    Log::info('Checking if target row is nested', [
                        'targetRowId' => $targetRowId,
                        'isTopLevelRow' => $isTopLevelRow,
                        'totalTopLevelRows' => count($this->rows),
                        'topLevelRowIds' => array_keys($this->rows),
                    ]);

                    $foundParentRowId = null;
                    if (! $isTopLevelRow) {
                        $foundParentRowId = $this->findBlockInNestedRows(rows: $this->rows, targetBlockId: $targetRowId);
                        Log::info('Search result for nested row', [
                            'targetRowId' => $targetRowId,
                            'foundParentRowId' => $foundParentRowId,
                        ]);
                    }

                    $isNestedRow = ! $isTopLevelRow && $foundParentRowId !== null;

                    Log::info('Final determination', [
                        'isTopLevelRow' => $isTopLevelRow,
                        'isNestedRow' => $isNestedRow,
                        'foundParentRowId' => $foundParentRowId,
                    ]);

                    if ($isTopLevelRow || $isNestedRow) {
                        // Dispatch block-added event - RowBlock will handle adding it to its blocks array
                        Log::info('Dispatching block-added for paste inside', [
                            'rowId' => $targetRowId,
                            'blockId' => $blockId,
                            'blockAlias' => $nestedRowAlias,
                            'blocksCount' => count($blocks),
                            'isTopLevelRow' => $isTopLevelRow,
                            'isNestedRow' => $isNestedRow,
                        ]);

                        $this->dispatch(
                            'block-added',
                            rowId: $targetRowId,
                            blockId: $blockId,
                            blockAlias: $nestedRowAlias,
                            properties: $nestedRowProperties,
                            blocks: $blocks,
                            beforeBlockId: null,
                            afterBlockId: null
                        );

                        Log::info('RowBlock paste inside dispatched', [
                            'targetRowId' => $targetRowId,
                            'newBlockId' => $blockId,
                            'isNested' => $isNestedRow,
                        ]);

                        // Dispatch event for scroll and selection
                        $this->dispatch('row-pasted', rowId: $blockId);

                        $this->dispatch(
                            'notify',
                            message: __('Row pasted inside successfully'),
                            type: 'success'
                        );

                        return;
                    } else {
                        Log::warning('Target row not found for inside paste (neither top-level nor nested)', [
                            'targetRowId' => $targetRowId,
                        ]);

                        return;
                    }
                }

                // Check if target is a nested row (not in top-level rows)
                $isNestedRow = false;
                if ($targetRowId && ! isset($this->rows[$targetRowId])) {
                    $isNestedRow = true;
                }

                // Handle before/after paste to nested rows - paste as sibling nested row block
                if ($isNestedRow && ($position === 'before' || $position === 'after')) {
                    Log::info('Pasting RowBlock as sibling to nested row', [
                        'nestedRowId' => $targetRowId,
                        'position' => $position,
                    ]);

                    // Find which top-level row contains this nested row block
                    $parentRowId = null;
                    foreach ($this->rows as $rowId => $row) {
                        if (isset($row['blocks'][$targetRowId])) {
                            $parentRowId = $rowId;
                            break;
                        }
                    }

                    if ($parentRowId) {
                        $blockId = uniqid();

                        // Generate new IDs for nested blocks
                        $blocks = [];
                        foreach ($data['blocks'] as $oldBlockId => $block) {
                            $blocks[uniqid()] = $block;
                        }

                        // Create the nested RowBlock as a sibling
                        $nestedRowBlock = [
                            'alias' => $data['blockAlias'] ?? 'page-builder-trinavo-livewire-page-builder-http-livewire-row-block',
                            'properties' => $data['properties'] ?? [],
                            'blocks' => $blocks,
                        ];

                        // Use targetRowId as the reference block for positioning
                        $targetBlockId = $targetRowId;

                        // DON'T modify $this->rows directly - let RowBlock handle it via block-added event
                        $beforeBlockId = null;
                        $afterBlockId = null;

                        if ($position === 'before') {
                            $beforeBlockId = $targetBlockId;
                        } else {
                            $afterBlockId = $targetBlockId;
                        }

                        Log::info('Dispatching block-added for nested row paste', [
                            'parentRowId' => $parentRowId,
                            'blockId' => $blockId,
                            'beforeBlockId' => $beforeBlockId,
                            'afterBlockId' => $afterBlockId,
                        ]);

                        // Dispatch block-added event - parent RowBlock will handle adding it
                        $this->dispatch(
                            'block-added',
                            rowId: $parentRowId,
                            blockId: $blockId,
                            blockAlias: $nestedRowBlock['alias'],
                            properties: $nestedRowBlock['properties'],
                            blocks: $nestedRowBlock['blocks'],
                            beforeBlockId: $beforeBlockId,
                            afterBlockId: $afterBlockId
                        );

                        Log::info('RowBlock paste dispatched as sibling to nested row', [
                            'parentRowId' => $parentRowId,
                            'newBlockId' => $blockId,
                            'position' => $position,
                            'targetBlockId' => $targetBlockId,
                        ]);

                        // Dispatch event for scroll and selection (using blockId since nested rows are blocks)
                        $this->dispatch('row-pasted', rowId: $blockId);

                        $this->dispatch(
                            'notify',
                            message: __('Row pasted successfully'),
                            type: 'success'
                        );

                        return;
                    }

                    Log::warning('Could not find parent row for nested row paste', [
                        'targetRowId' => $targetRowId,
                    ]);

                    return;
                }

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

                Log::info('Row pasted successfully', [
                    'newRowId' => $rowId,
                    'position' => $position,
                    'afterRowId' => $afterRowId ?? 'null',
                    'beforeRowId' => $beforeRowId ?? 'null',
                ]);

                // Dispatch event for scroll and selection
                $this->dispatch('row-pasted', rowId: $rowId);

                $this->dispatch(
                    'notify',
                    message: __('Row pasted successfully'),
                    type: 'success'
                );
            }
            // Handle Block paste
            elseif ($data['type'] === 'Block') {
                Log::info('Pasting Block', [
                    'blockAlias' => $data['blockAlias'] ?? 'unknown',
                    'targetRowId' => $targetRowId,
                    'targetBlockId' => $targetBlockId,
                    'position' => $position,
                ]);

                $blockId = uniqid();
                $block = [
                    'alias' => $data['blockAlias'],
                    'properties' => $data['properties'] ?? [],
                ];

                // Include nested blocks if this is a RowBlock with blocks
                if (isset($data['blocks']) && ! empty($data['blocks'])) {
                    $block['blocks'] = $data['blocks'];
                }

                // Find the row of the target block (including nested rows)
                $parentRowId = null;

                // Only search for target block if targetBlockId is provided
                if ($targetBlockId) {
                    // First check top-level rows
                    foreach ($this->rows as $rowId => $row) {
                        if (isset($row['blocks'][$targetBlockId])) {
                            $parentRowId = $rowId;
                            break;
                        }
                    }

                    // If not found in top-level, search in nested RowBlocks
                    if (! $parentRowId) {
                        $parentRowId = $this->findBlockInNestedRows($this->rows, $targetBlockId);
                    }
                }

                // If no parent found yet and targetRowId is provided, use it
                if (! $parentRowId && $targetRowId) {
                    $parentRowId = $targetRowId;
                }

                // Check if parent is a nested RowBlock or a top-level row
                $isNestedRow = false;
                if ($parentRowId && ! isset($this->rows[$parentRowId])) {
                    // Parent is a nested RowBlock, not a top-level row
                    $isNestedRow = true;
                }

                Log::info('Parent row found for block paste', [
                    'parentRowId' => $parentRowId ?? 'null',
                    'targetBlockId' => $targetBlockId,
                    'isNestedRow' => $isNestedRow,
                    'position' => $position,
                ]);

                // Handle special case: pasting to a nested row with before/after position
                // In this case, we need to paste as a sibling in the parent row, not inside the nested row
                if ($isNestedRow && ($position === 'before' || $position === 'after') && ! $targetBlockId) {
                    Log::info('Pasting Block as sibling to nested row (outside)', [
                        'nestedRowId' => $parentRowId,
                        'position' => $position,
                    ]);

                    // Find which top-level row contains this nested row block
                    $actualParentRowId = null;
                    foreach ($this->rows as $rowId => $row) {
                        if (isset($row['blocks'][$parentRowId])) {
                            $actualParentRowId = $rowId;
                            break;
                        }
                    }

                    if ($actualParentRowId) {
                        // Use the nested row as the target block for positioning
                        $targetBlockId = $parentRowId;
                        $parentRowId = $actualParentRowId;
                        $isNestedRow = false;

                        Log::info('Found parent row for nested row', [
                            'actualParentRowId' => $actualParentRowId,
                            'nestedRowBlockId' => $targetBlockId,
                        ]);
                    }
                }

                if ($parentRowId) {
                    if ($isNestedRow && $position === 'inside') {
                        // Parent is a nested RowBlock and position is 'inside' - add block inside the nested row
                        Log::info('Dispatching add-block-to-nested-row event for paste inside', [
                            'rowId' => $parentRowId,
                            'blockAlias' => $data['blockAlias'],
                            'position' => 'inside',
                        ]);

                        // Dispatch the event to add block inside the nested row
                        $this->dispatch('add-block-to-nested-row',
                            rowId: $parentRowId,
                            blockAlias: $data['blockAlias'],
                            blockPageName: $data['blockPageName'] ?? null,
                            beforeBlockId: null,
                            afterBlockId: null,
                            replaceBlockId: null,
                            position: 'inside',
                            properties: $data['properties'] ?? null,
                            blocks: $data['blocks'] ?? null
                        );

                        $this->dispatch(
                            'notify',
                            message: __('Block pasted successfully'),
                            type: 'success'
                        );
                    } elseif ($targetBlockId) {
                        // Parent row with target block (top-level or nested)
                        // Create positioning parameters based on position value
                        $beforeBlockId = null;
                        $afterBlockId = null;

                        if ($position === 'before') {
                            $beforeBlockId = $targetBlockId;
                        } else { // position === 'after'
                            $afterBlockId = $targetBlockId;
                        }

                        // DON'T modify $this->rows directly - let RowBlock handle it via block-added event
                        // RowBlock will add the block to its local state and sync back via sync-nested-row-data

                        Log::info('Dispatching block-added for paste', [
                            'isNestedRow' => $isNestedRow,
                            'parentRowId' => $parentRowId,
                            'blockId' => $blockId,
                            'beforeBlockId' => $beforeBlockId,
                            'afterBlockId' => $afterBlockId,
                        ]);

                        // Dispatch to row-block component with position parameters
                        $this->dispatch(
                            'block-added',
                            rowId: $parentRowId,
                            blockId: $blockId,
                            blockAlias: $data['blockAlias'],
                            properties: $block['properties'],
                            blocks: $block['blocks'] ?? null,
                            beforeBlockId: $beforeBlockId,
                            afterBlockId: $afterBlockId
                        );

                        // Dispatch event for scroll and selection
                        $this->dispatch('block-pasted', blockId: $blockId);

                        $this->dispatch(
                            'notify',
                            message: __('Block pasted successfully'),
                            type: 'success'
                        );
                    } else {
                        // If no target block, add to the end of row
                        $beforeBlockId = null;
                        $afterBlockId = null;

                        $this->rows[$parentRowId]['blocks'][$blockId] = $block;

                        // Dispatch to row-block component
                        $this->dispatch(
                            'block-added',
                            rowId: $parentRowId,
                            blockId: $blockId,
                            blockAlias: $data['blockAlias'],
                            properties: $block['properties']
                        );

                        Log::info('Block pasted successfully to top-level row', [
                            'newBlockId' => $blockId,
                            'parentRowId' => $parentRowId,
                            'position' => $position,
                            'beforeBlockId' => $beforeBlockId,
                            'afterBlockId' => $afterBlockId,
                        ]);

                        // Dispatch event for scroll and selection
                        $this->dispatch('block-pasted', blockId: $blockId);

                        $this->dispatch(
                            'notify',
                            message: __('Block pasted successfully'),
                            type: 'success'
                        );
                    }
                } else {
                    Log::warning('No parent row found for block paste', [
                        'targetRowId' => $targetRowId,
                        'targetBlockId' => $targetBlockId,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error pasting from clipboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'targetRowId' => $targetRowId,
                'targetBlockId' => $targetBlockId,
                'position' => $position,
            ]);
            report($e);
            $this->dispatch(
                'notify',
                message: 'Failed to paste: '.$e->getMessage(),
                type: 'error'
            );
        }
    }

    /**
     * Add a block to a nested RowBlock in the $rows structure
     */
    private function addBlockToNestedRowInStructure(
        array &$rows,
        string $targetRowBlockId,
        string $newBlockId,
        array $newBlock,
        ?string $targetBlockId = null,
        string $position = 'after',
        int $depth = 0
    ): bool {
        Log::info('addBlockToNestedRowInStructure searching', [
            'targetRowBlockId' => $targetRowBlockId,
            'depth' => $depth,
            'rowsCount' => count($rows),
            'rowKeys' => array_keys($rows),
        ]);

        foreach ($rows as $rowId => &$row) {
            Log::info('Checking row/block', [
                'id' => $rowId,
                'hasBlocks' => isset($row['blocks']),
                'blocksCount' => isset($row['blocks']) ? count($row['blocks']) : 0,
            ]);

            if (! isset($row['blocks'])) {
                continue;
            }

            foreach ($row['blocks'] as $blockId => &$block) {
                Log::info('Checking nested block', [
                    'blockId' => $blockId,
                    'isTarget' => $blockId === $targetRowBlockId,
                    'hasNestedBlocks' => isset($block['blocks']),
                ]);

                // Check if this is the target RowBlock
                if ($blockId === $targetRowBlockId && isset($block['blocks'])) {
                    Log::info('Found target RowBlock, adding block', [
                        'targetRowBlockId' => $targetRowBlockId,
                        'newBlockId' => $newBlockId,
                        'targetBlockId' => $targetBlockId,
                        'position' => $position,
                        'currentBlocksCount' => count($block['blocks']),
                    ]);

                    // Add block with positioning
                    if ($targetBlockId && $position) {
                        // Find position of target block
                        $blockKeys = array_keys($block['blocks']);
                        $targetIndex = array_search($targetBlockId, $blockKeys);

                        if ($targetIndex !== false) {
                            $insertIndex = $position === 'after' ? $targetIndex + 1 : $targetIndex;

                            // Split and insert
                            $before = array_slice($block['blocks'], 0, $insertIndex, true);
                            $after = array_slice($block['blocks'], $insertIndex, null, true);

                            $block['blocks'] = $before + [$newBlockId => $newBlock] + $after;

                            Log::info('Block inserted at position', [
                                'targetIndex' => $targetIndex,
                                'insertIndex' => $insertIndex,
                                'totalBlocks' => count($block['blocks']),
                            ]);
                        } else {
                            // Target block not found, add to end
                            $block['blocks'][$newBlockId] = $newBlock;
                        }
                    } else {
                        // No target block, add to end
                        $block['blocks'][$newBlockId] = $newBlock;
                    }

                    return true;
                }

                // If this block has nested blocks, search recursively
                if (isset($block['blocks']) && is_array($block['blocks'])) {
                    Log::info('Recursing into nested block', [
                        'blockId' => $blockId,
                        'nestedBlocksCount' => count($block['blocks']),
                        'nestedBlockKeys' => array_keys($block['blocks']),
                        'nextDepth' => $depth + 1,
                    ]);

                    // Recurse directly into the nested blocks
                    // We need to search within this block's nested blocks
                    foreach ($block['blocks'] as $nestedBlockId => &$nestedBlock) {
                        if ($nestedBlockId === $targetRowBlockId && isset($nestedBlock['blocks'])) {
                            Log::info('Found target in immediate nested blocks', [
                                'targetRowBlockId' => $targetRowBlockId,
                                'depth' => $depth + 1,
                            ]);

                            // Add block with positioning
                            if ($targetBlockId && $position) {
                                $blockKeys = array_keys($nestedBlock['blocks']);
                                $targetIndex = array_search($targetBlockId, $blockKeys);

                                if ($targetIndex !== false) {
                                    $insertIndex = $position === 'after' ? $targetIndex + 1 : $targetIndex;
                                    $before = array_slice($nestedBlock['blocks'], 0, $insertIndex, true);
                                    $after = array_slice($nestedBlock['blocks'], $insertIndex, null, true);
                                    $nestedBlock['blocks'] = $before + [$newBlockId => $newBlock] + $after;
                                } else {
                                    $nestedBlock['blocks'][$newBlockId] = $newBlock;
                                }
                            } else {
                                $nestedBlock['blocks'][$newBlockId] = $newBlock;
                            }

                            return true;
                        }

                        // Continue recursing deeper if this nested block also has blocks
                        if (isset($nestedBlock['blocks']) && is_array($nestedBlock['blocks'])) {
                            $tempRows = [$nestedBlockId => $nestedBlock];
                            if ($this->addBlockToNestedRowInStructure(
                                $tempRows,
                                $targetRowBlockId,
                                $newBlockId,
                                $newBlock,
                                $targetBlockId,
                                $position,
                                $depth + 2
                            )) {
                                $nestedBlock = $tempRows[$nestedBlockId];

                                return true;
                            }
                        }
                    }
                }
            }
        }

        Log::info('Target RowBlock not found at this level', [
            'targetRowBlockId' => $targetRowBlockId,
            'depth' => $depth,
        ]);

        return false;
    }

    /**
     * Recursively search for a block ID in nested RowBlocks
     * Returns the parent row ID (which could be a nested RowBlock ID)
     */
    private function findBlockInNestedRows(array $rows, string $targetBlockId, int $depth = 0): ?string
    {
        $indent = str_repeat('  ', $depth);
        Log::info("{$indent}findBlockInNestedRows: Searching at depth {$depth}", [
            'targetBlockId' => $targetBlockId,
            'rowKeys' => array_keys($rows),
            'depth' => $depth,
        ]);

        foreach ($rows as $rowId => $row) {
            Log::info("{$indent}Checking row: {$rowId}", [
                'hasBlocks' => isset($row['blocks']),
                'blocksCount' => isset($row['blocks']) ? count($row['blocks']) : 0,
            ]);

            if (! isset($row['blocks'])) {
                continue;
            }

            foreach ($row['blocks'] as $blockId => $block) {
                Log::info("{$indent}  Checking block: {$blockId}", [
                    'isTarget' => $blockId === $targetBlockId,
                    'hasNestedBlocks' => isset($block['blocks']),
                ]);

                // IMPORTANT: Check if THIS blockId is the target we're looking for
                if ($blockId === $targetBlockId) {
                    Log::info("{$indent}  ✅ FOUND! blockId matches targetBlockId", [
                        'blockId' => $blockId,
                        'parentRowId' => $rowId,
                    ]);

                    return $rowId; // Return the parent row ID
                }

                // Check if this block contains the target
                if (isset($block['blocks'][$targetBlockId])) {
                    Log::info("{$indent}  ✅ Found target in block's nested blocks", [
                        'nestedRowBlockId' => $blockId,
                        'targetBlockId' => $targetBlockId,
                    ]);

                    return $blockId; // Return the RowBlock ID as the parent
                }

                // If this block is a RowBlock with nested blocks, search recursively
                if (isset($block['blocks']) && is_array($block['blocks'])) {
                    Log::info("{$indent}  Recursing into block {$blockId}");
                    $nestedResult = $this->findBlockInNestedRows([$blockId => $block], targetBlockId: $targetBlockId, depth: $depth + 1);
                    if ($nestedResult) {
                        Log::info("{$indent}  ✅ Found in recursive search");

                        return $nestedResult;
                    }
                }
            }
        }

        Log::info("{$indent}❌ Target not found at depth {$depth}");

        return null;
    }

    /**
     * Get block data from a nested row
     * Returns the block data array
     */
    private function getBlockFromNestedRow(array $rows, string $parentRowId, string $targetBlockId): ?array
    {
        foreach ($rows as $rowId => $row) {
            if (! isset($row['blocks'])) {
                continue;
            }

            foreach ($row['blocks'] as $blockId => $block) {
                // Check if this is the parent row we're looking for
                if ($blockId === $parentRowId && isset($block['blocks'][$targetBlockId])) {
                    Log::info('✅ Retrieved block from nested row', [
                        'parentRowId' => $parentRowId,
                        'targetBlockId' => $targetBlockId,
                    ]);

                    return $block['blocks'][$targetBlockId];
                }

                // If this block has nested blocks, search recursively
                if (isset($block['blocks']) && is_array($block['blocks'])) {
                    $result = $this->getBlockFromNestedRow([$blockId => $block], $parentRowId, $targetBlockId);
                    if ($result) {
                        return $result;
                    }
                }
            }
        }

        return null;
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

            // For BuilderPageBlock, set the blockPageName property before getting the label
            if ($blockClass === \Trinavo\LivewirePageBuilder\Http\Livewire\BuilderPageBlock::class) {
                $blockInstance->blockPageName = $block['properties']['blockPageName'] ?? null;
            }

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

        // Copy the components with new unique IDs
        $this->rows = $this->regenerateComponentIds($sourcePage->components);

        // Save the current page with copied components
        $this->savePage();

        // Show success message
        $this->dispatch('notify',
            message: __('Components copied successfully from :page', ['page' => $this->getPageLabel($sourcePageKey)]),
            type: 'success'
        );
    }

    /**
     * Regenerate unique IDs for all components
     */
    private function regenerateComponentIds(array $components): array
    {
        $newComponents = [];

        foreach ($components as $row) {
            $newRowId = uniqid();
            $newRow = [
                'properties' => $row['properties'] ?? [],
                'blocks' => [],
            ];

            if (isset($row['blocks'])) {
                $newRow['blocks'] = $this->regenerateBlockIds($row['blocks']);
            }

            $newComponents[$newRowId] = $newRow;
        }

        return $newComponents;
    }

    /**
     * Recursively regenerate IDs for blocks
     */
    private function regenerateBlockIds(array $blocks): array
    {
        $newBlocks = [];

        foreach ($blocks as $block) {
            $newBlockId = uniqid();
            $newBlock = [
                'alias' => $block['alias'],
                'properties' => $block['properties'] ?? [],
            ];

            // If this block has nested blocks (like RowBlock), regenerate their IDs too
            if (isset($block['blocks'])) {
                $newBlock['blocks'] = $this->regenerateBlockIds($block['blocks']);
            }

            $newBlocks[$newBlockId] = $newBlock;
        }

        return $newBlocks;
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

            // Log block data for BuilderPageBlock
            if ($block['alias'] === 'builder-page-block') {
                Log::info('getBlocksWithNesting - BuilderPageBlock data', [
                    'blockId' => $blockId,
                    'alias' => $block['alias'],
                    'properties' => $block['properties'] ?? 'NO PROPERTIES',
                    'blockPageName_in_properties' => $block['properties']['blockPageName'] ?? 'NOT SET',
                ]);
            }

            $blockInstance = app($blockClass);

            // For BuilderPageBlock, set the blockPageName property before getting the label
            if ($blockClass === \Trinavo\LivewirePageBuilder\Http\Livewire\BuilderPageBlock::class) {
                $blockInstance->blockPageName = $block['properties']['blockPageName'] ?? null;
            }

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
