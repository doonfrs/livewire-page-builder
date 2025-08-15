<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

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

        $this->rows = $this->page->components ? json_decode($this->page->components, true) : [];
    }

    #[On('save-page')]
    public function savePage()
    {
        $this->page->components = json_encode($this->rows);
        $this->page->saveOrFail();
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

        $properties = app($blockClass)->getPropertyValues();
        if ($blockPageName) {
            $properties['blockPageName'] = $blockPageName;
        }
        $blockId = uniqid();
        $block = [
            'alias' => $blockAlias,
            'properties' => $properties,
        ];
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

            // Return block data with iconHtml added
            return [
                'alias' => $block['alias'],
                'label' => $block['label'],
                'blockPageName' => $block['blockPageName'] ?? null,
                'iconHtml' => '<x-'.$iconComponent.' class="w-10 h-10" />',
            ];
        })->values()->toArray();
    }

    public function addBlockToModalRow($blockAlias, $blockPageName = null)
    {
        if ($this->modalRowId) {
            $this->addBlockToRow($this->modalRowId, $blockAlias, $blockPageName);
            $this->closeBlockModal();
        }
    }

    #[On('updateBlockProperty')]
    public function updateBlockProperty($rowId, $blockId, $propertyName, $value)
    {
        if ($rowId) {
            $this->rows[$rowId]['properties'][$propertyName] = $value;
        } else {
            foreach ($this->rows as $rowId => $row) {
                if (isset($row['blocks'][$blockId])) {
                    $this->rows[$rowId]['blocks'][$blockId]['properties'][$propertyName] = $value;
                    break;
                }
            }
        }
        $this->skipRender();
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
        if (isset($this->rows[$rowId])) {
            unset($this->rows[$rowId]);
        }
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
                'iconHtml' => '<x-'.$iconComponent.' class="w-10 h-10" />',
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
                foreach ($row['blocks'] as $blockId => $block) {
                    $blockClass = app(PageBuilderService::class)->getClassNameFromAlias($block['alias']);
                    if (! $blockClass) {
                        continue;
                    }

                    $blockInstance = app($blockClass);
                    $label = $blockInstance->getPageBuilderLabel();
                    $icon = $blockInstance->getPageBuilderIcon() ?? 'heroicon-o-cube';

                    $blocks[] = [
                        'id' => $blockId,
                        'rowId' => $rowId,
                        'alias' => $block['alias'],
                        'label' => $label,
                        'icon' => $icon,
                    ];
                }
            }
        }

        return $blocks;
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
                $components = json_decode($page->components, true);
                $hasComponents = ! empty($components);
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
        $this->rows = json_decode($sourcePage->components, true);

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
            $blocks = [];

            foreach ($row['blocks'] as $blockId => $block) {
                $blockClass = app(PageBuilderService::class)->getClassNameFromAlias($block['alias']);
                if (! $blockClass) {
                    continue;
                }

                $blockInstance = app($blockClass);
                $label = $blockInstance->getPageBuilderLabel();
                $icon = $blockInstance->getPageBuilderIcon() ?? 'heroicon-o-cube';

                $blocks[] = [
                    'id' => $blockId,
                    'alias' => $block['alias'],
                    'label' => $label,
                    'icon' => $icon,
                ];
            }

            $grouped[$rowId] = [
                'blocks' => $blocks,
            ];
        }

        return $grouped;
    }
}
