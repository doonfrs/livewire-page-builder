<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class BlockProperties extends Component
{

    public $blockData = null;
    public $rowId = null;
    public $blockId = null;

    public function mount() {}

    public function render()
    {

        return view('page-builder::block-properties');
    }

    public function updateBlockProperty($rowId, $blockId, $property, $value)
    {
        $this->dispatch('updateBlockProperty', $rowId, $blockId, $property, $value);
    }
}
