<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

use Livewire\Component;

class RichTextProperty extends Component
{
    public $rowId = null;

    public $blockId = null;

    public $propertyName;

    public $propertyLabel;

    public $currentValue;

    public function updatedCurrentValue()
    {
        $this->updateProperty();
    }

    protected function updateProperty()
    {
        $this->dispatch('updateBlockProperty', $this->rowId, $this->blockId, $this->propertyName, $this->currentValue);
    }

    public function render()
    {
        return view('page-builder::livewire.builder.block-properties.rich-text');
    }
}
