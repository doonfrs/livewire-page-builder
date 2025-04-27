<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class BlockProperties extends Component
{

    public ?string $selectedBlockId = null;
    public ?string $selectedRowId = null;
    
    public function mount()
    {

    }

    #[On('block-selected')] 
    public function blockSelected($blockId)
    {
        $this->selectedBlockId = $blockId;
    }

    #[On('row-selected')] 
    public function rowSelected($rowId)
    {
        $this->selectedRowId = $rowId;
    }

    public function render()
    {
        return view('livewire-page-builder::block-properties');
    }
}
