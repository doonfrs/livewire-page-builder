<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Component;
use Trinavo\LivewirePageBuilder\Services\BlockService;

class Row extends Component
{

    public array $blocks = [];
    public $availableBlocks = [];
    public ?string $rowId;

    public function mount()
    {
        $this->availableBlocks = config('page-builder.blocks');
    }

    public function addBlock($blockName)
    {
        $blockId = uniqid();
        $this->blocks[$blockId] = ['name' => $blockName];

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
