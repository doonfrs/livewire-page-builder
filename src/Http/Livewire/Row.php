<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Component;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;

class Row extends Component
{

    public array $blocks = [];
    public $availableBlocks = [];
    public ?string $rowId;

    public function mount()
    {
        $this->availableBlocks = app(PageBuilderService::class)->getAvailableBlocks();
    }

    public function addBlock($blockAlias)
    {
        $blockId = uniqid();
        $this->blocks[$blockId] = ['alias' => $blockAlias];

        $this->dispatch('block-added', $blockId);
    }

    public function rowSelected($rowId)
    {
        $this->dispatch('row-selected', $rowId);
    }

    public function render()
    {
        return view('page-builder::row');
    }
}
