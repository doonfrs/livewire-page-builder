<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Component;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;

class Row extends Component
{

    public array $blocks = [];
    public ?string $rowId;



    public function rowSelected($rowId)
    {
        $this->dispatch('row-selected', $rowId);
    }

    public function openBlockModal()
    {
        $this->dispatch('openBlockModal', $this->rowId)->to('page-editor');
    }

    public function selectBlock($blockId)
    {
        $this->dispatch('selectBlock', $this->rowId, $blockId);
    }

    public function selectRow()
    {
        $this->dispatch('selectBlock', $this->rowId, null);
    }

    public function render()
    {
        return view('page-builder::row');
    }
}
