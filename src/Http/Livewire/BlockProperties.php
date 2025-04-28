<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class BlockProperties extends Component
{

    public $blockData = null;
    public $rowData = null;

    public function mount() {}

    public function render()
    {

        return view('page-builder::block-properties');
    }
}
