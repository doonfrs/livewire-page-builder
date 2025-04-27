<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Component;

class PageEditor extends Component
{
    public $rows = [];
    public $availableBlocks = [];

    public function mount()
    {
        $this->availableBlocks = config('page-builder.blocks');
    }

    public function addRow()
    {
        $rowId = uniqid();
        $this->rows[$rowId] = [
            'widgets' => [],
            'properties' => []
        ];

        $this->dispatch('rowAdded', $rowId);

    }

    public function updateRowOrder($rowIds)
    {
        dd($rowIds);
    }

    public function addWidget($rowId, $widgetName)
    {
        $this->rows[$rowId]['widgets'][uniqid()] = [
            'name' => $widgetName,
            'properties' => []
        ];
    }

    public function save() {}

    public function render()
    {
        return view('page-builder::page-editor')
            ->layout('page-builder::layouts.app');
    }
}
