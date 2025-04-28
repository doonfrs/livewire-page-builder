<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Livewire\Component;
use Livewire\Attributes\On;

class PageEditor extends Component
{
    public $rows = [];
    public $availableBlocks = [];
    public ?string $selectedRowId = null;
    public ?string $selectedBlockId = null;
    public bool $showBlockModal = false;
    public string $blockFilter = '';
    public ?string $modalRowId = null;

    public function mount()
    {
        $this->availableBlocks = app(PageBuilderService::class)->getAvailableBlocks();
    }

    public function addRow()
    {
        $rowId = uniqid();
        $this->rows[$rowId] = [
            'blocks' => [],
            'properties' => []
        ];

        $this->dispatch('rowAdded', $rowId);
    }

    public function updateRowOrder($rowIds)
    {
        dd($rowIds);
    }

    public function addBlock($rowId, $blockAlias)
    {
        // Find the class name for this alias
        $blockClass = null;
        foreach ($this->availableBlocks as $block) {
            if ($block['alias'] === $blockAlias) {
                $blockClass = $block['class'];
                break;
            }
        }

        $this->rows[$rowId]['blocks'][uniqid()] = [
            'alias' => $blockAlias,
            'properties' => []
        ];
    }

    public function save() {}

    #[On('selectBlock')]
    public function selectBlock($rowId, $blockId)
    {
        $this->selectedRowId = $rowId;
        $this->selectedBlockId = $blockId;
        // Debug message for confirmation
        session()->flash('debug', "Block selected: $rowId - $blockId");
    }

    public function getSelectedBlock()
    {
        if ($this->selectedRowId && $this->selectedBlockId) {
            return $this->rows[$this->selectedRowId]['blocks'][$this->selectedBlockId] ?? null;
        }
        return null;
    }

    public function getSelectedBlockDataProperty()
    {
        return $this->getSelectedBlock();
    }

    public function getSelectedBlockClassProperty()
    {
        $block = $this->getSelectedBlock();
        return $block['class'] ?? null;
    }

    #[On('addBlockToRow')]
    public function addBlockToRow($rowId, $blockAlias)
    {
        // Find the class name for this alias
        $blockClass = null;
        foreach ($this->availableBlocks as $block) {
            if ($block['alias'] === $blockAlias) {
                $blockClass = $block['class'];
                break;
            }
        }

        $this->rows[$rowId]['blocks'][uniqid()] = [
            'alias' => $blockAlias,
            'class' => $blockClass, // Store the real class name!
            'properties' => []
        ];
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
        if (!$this->blockFilter) {
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

    public function render()
    {
        return view('page-builder::page-editor')
            ->layout('page-builder::layouts.app');
    }
}
