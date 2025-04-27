<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Component;

class Block extends Component
{
    public $cols = 4;
    public $widgetName = null;
    public $widgetId = null;
    public array $widgetProperties = [];

    public function mount()
    {
        
    }

    public function blockSelected($blockId)
    {
        $this->dispatch('block-selected', $blockId);
    }

    public function render()
    {
        return view('livewire-page-builder::block');
    }
}
