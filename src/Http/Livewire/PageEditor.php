<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;

class PageEditor extends Component
{
    public $rows = [];

    public $availableBlocks = [];

    public bool $showBlockModal = false;

    public string $blockFilter = '';

    public ?string $modalRowId = null;

    public ?string $selectedBlockId = null;

    public ?string $selectedRowId = null;

    public function mount()
    {
        $this->availableBlocks = app(PageBuilderService::class)->getAvailableBlocks();
    }

    public function addRow()
    {
        $rowId = uniqid();
        $this->rows[$rowId] = [
            'blocks' => [],
            'properties' => app(RowBlock::class)->getPropertyValues()];
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
            // Swap the current row with the previous one
            $temp = $newOrder[$currentIndex - 1];
            $newOrder[$currentIndex - 1] = $newOrder[$currentIndex];
            $newOrder[$currentIndex] = $temp;

            // Rebuild the rows array in the new order
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
            // Swap the current row with the next one
            $temp = $newOrder[$currentIndex + 1];
            $newOrder[$currentIndex + 1] = $newOrder[$currentIndex];
            $newOrder[$currentIndex] = $temp;

            // Rebuild the rows array in the new order
            $this->rows = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $this->rows[$id]])->toArray();
        }
    }

    #[On('deleteRow')]
    public function deleteRow($rowId)
    {
        if (isset($this->rows[$rowId])) {
            unset($this->rows[$rowId]);
        }
    }

    public function render()
    {
        return view('page-builder::page-editor', [
            'selectedRowId' => $this->selectedRowId ?? null,
            'selectedBlockId' => $this->selectedBlockId ?? null,
        ])->layout('page-builder::layouts.app');
    }
}
