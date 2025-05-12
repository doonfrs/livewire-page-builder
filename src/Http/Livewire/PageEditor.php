<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;

class PageEditor extends Component
{
    public $rows = [];

    public $availableBlocks = [];

    public bool $showBlockModal = false;

    public string $blockFilter = '';

    public ?string $modalRowId = null;

    public ?string $beforeBlockId = null;

    public ?string $afterBlockId = null;

    public ?string $pageKey = null;

    public ?string $pageTheme = null;

    public BuilderPage $page;

    public function mount()
    {
        $this->availableBlocks = app(PageBuilderService::class)->getAvailableBlocks();

        $this->pageKey = request()->route('pageKey');
        $this->pageTheme = request()->route('pageTheme');

        $this->page = BuilderPage::firstOrCreate([
            'key' => $this->pageKey,
            'theme' => $this->pageTheme,
        ]);

        $this->rows = $this->page->components ? json_decode($this->page->components, true) : [];

    }

    #[On('save-page')]
    public function savePage()
    {
        $this->page->components = json_encode($this->rows);
        $this->page->saveOrFail();
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

        $this->dispatch('row-added',
            rowId: $rowId,
            properties: $row['properties']);
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

        $this->dispatch('blockAdded', $rowId, $blockId, $blockAlias, $block['properties'], $this->beforeBlockId, $this->afterBlockId)->to('row-block');
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
        $this->skipRender();
    }

    public function render()
    {
        return view('page-builder::livewire.builder.page-editor', [
        ])->layout('page-builder::layouts.app');
    }
}
