<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

use Livewire\Component;

class SelectProperty extends Component
{
    public $propertyName;

    public $currentValue;

    public $propertyLabel;

    public $propertyOptions;

    public $defaultValue;

    public $rowId;

    public $blockId;

    public function render()
    {
        return view('page-builder::livewire.builder.block-properties.select-property');
    }

    public function updateProperty($value)
    {
        $this->dispatch('updateBlockProperty', $this->rowId, $this->blockId, $this->propertyName, $value);
    }
}
