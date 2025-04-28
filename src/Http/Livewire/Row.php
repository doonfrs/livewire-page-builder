<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Component;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;

class Row extends Component
{

    public array $blocks = [];
    public $availableBlocks = [];
    public ?string $rowId;
    public bool $showBlockModal = false;
    public string $blockFilter = '';

    public function mount()
    {
        $this->availableBlocks = app(PageBuilderService::class)->getAvailableBlocks();
    }

    public function addBlock($blockAlias)
    {
        $blockId = uniqid();
        $this->blocks[$blockId] = ['alias' => $blockAlias];
        $this->showBlockModal = false;
        $this->dispatch('block-added', $blockId);
    }

    public function rowSelected($rowId)
    {
        $this->dispatch('row-selected', $rowId);
    }

    public function openBlockModal()
    {
        $this->showBlockModal = true;
        $this->blockFilter = '';
    }

    public function closeBlockModal()
    {
        $this->showBlockModal = false;
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

    public function render()
    {
        return view('page-builder::row');
    }
}
