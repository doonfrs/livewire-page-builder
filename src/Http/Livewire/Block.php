<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Component;

class Block extends Component
{
    public $cols = 4;
    public $blockName = null;
    public $blockId = null;
    public array $blockProperties = [];

    public function mount() {}

    public function blockSelected($blockId)
    {
        $this->dispatch('block-selected', $blockId);
    }

    public function render()
    {
        return view('page-builder::block');
    }
}
