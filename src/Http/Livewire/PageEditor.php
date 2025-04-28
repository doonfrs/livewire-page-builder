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

    public function addBlock($rowId, $blockName)
    {
        $this->rows[$rowId]['blocks'][uniqid()] = [
            'alias' => $blockName,
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

    public function getSelectedBlockClass()
    {
        $block = $this->getSelectedBlock();
        return $block['alias'] ?? null;
    }

    public function getSelectedBlockDataProperty()
    {
        return $this->getSelectedBlock();
    }

    public function getSelectedBlockClassProperty()
    {
        return $this->getSelectedBlockClass();
    }

    public function render()
    {
        return view('page-builder::page-editor')
            ->layout('page-builder::layouts.app');
    }
}
