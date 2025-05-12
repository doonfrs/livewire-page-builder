<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

use Livewire\Component;

class ImageProperty extends Component
{
    public $property;

    public $rowId;

    public $blockId;

    public function render()
    {
        return view('page-builder::livewire.builder.block-properties.image-property');
    }
}
