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

    public function addRow($afterRowId = null)
    {
        $rowId = uniqid();
        $rowBlock = app(RowBlock::class);
        $row = [
            'blocks' => [],
            'properties' => $rowBlock->getPropertyValues(),
        ];
        if ($afterRowId) {
            $this->rows = array_merge(
                array_slice($this->rows, 0, $afterRowId),
                [$rowId => $row],
                array_slice($this->rows, $afterRowId)
            );
        } else {
            $this->rows[$rowId] = $row;
        }

        $this->dispatch('row-added',
            rowId: $rowId,
            properties: $this->rows[$rowId]['properties']);
    }

    #[On('addBlockToRow')]
    public function addBlockToRow($rowId, $blockAlias)
    {
        $blockClass = null;
        foreach ($this->availableBlocks as $block) {
            if ($block['alias'] === $blockAlias) {
                $blockClass = $block['class'];
                break;
            }
        }

        $blockId = uniqid();
        $this->rows[$rowId]['blocks'][$blockId] = [
            'alias' => $blockAlias,
            'properties' => app($blockClass)->getPropertyValues(),
        ];

        $this->dispatch('blockAdded', $rowId, $blockId, $blockAlias)->to('row-block');
    }

    #[On('openBlockModal')]
    public function openBlockModal($rowId)
    {
        $this->showBlockModal = true;
        $this->blockFilter = '';
        $this->modalRowId = $rowId;
    }

    public function closeBlockModal()
    {
        $this->showBlockModal = false;
        $this->modalRowId = null;
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

    public function addBlockToModalRow($blockAlias)
    {
        if ($this->modalRowId) {
            $this->addBlockToRow($this->modalRowId, $blockAlias);
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
        return view('page-builder::builder.page-editor', [
        ])->layout('page-builder::layouts.app');
    }
}
