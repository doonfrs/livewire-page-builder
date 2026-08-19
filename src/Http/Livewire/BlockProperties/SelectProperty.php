<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

use Livewire\Component;
use Trinavo\LivewirePageBuilder\Support\Concerns\DispatchesBlockPropertyUpdate;

class SelectProperty extends Component
{
    use DispatchesBlockPropertyUpdate;

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
        $this->dispatchBlockPropertyUpdate($this->propertyName, $value);
    }
}
