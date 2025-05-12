<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

use Livewire\Component;

class SelectProperty extends Component
{
    public $property;

    public $rowId;

    public $blockId;

    public function render()
    {
        return view('page-builder::livewire.builder.block-properties.select-property');
    }
}
