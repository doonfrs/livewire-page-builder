<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class BlockProperties extends Component
{

    public ?string $selectedRowId = null;
    public ?string $selectedBlockId = null;
    public $blockData = null;
    public $blockClass = null;

    public function mount() {}

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
        // Debug info for the component
        $debug = [
            'selectedRowId' => $this->selectedRowId,
            'selectedBlockId' => $this->selectedBlockId,
            'blockClass' => $this->blockClass,
            'blockData' => $this->blockData ? 'Yes' : 'No',
        ];

        return view('page-builder::block-properties', [
            'debug' => $debug
        ]);
    }
}
