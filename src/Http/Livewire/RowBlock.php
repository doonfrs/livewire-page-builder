<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Attributes\On;
use Trinavo\LivewirePageBuilder\Support\Block;

class RowBlock extends Block
{
    public array $blocks = [];

    public ?string $rowId;

    public $properties = [];

    public function mount()
    {
        $this->properties = $this->getPropertyValues();
    }

    public function openBlockModal()
    {
        $this->dispatch('openBlockModal', $this->rowId)->to('page-editor');
    }

    public function render()
    {
        return view('page-builder::row', [
            'rowId' => $this->rowId,
            'properties' => $this->properties,
        ]);
    }

    public function rowSelected()
    {
        $this->dispatch(
            'row-selected',
            rowId: $this->rowId,
            properties: $this->properties,
        );

        $this->skipRender();
    }

    #[On('blockAdded')]
    public function blockAdded($rowId, $blockAlias)
    {
        if ($rowId != $this->rowId) {
            return;
        }
        $this->blocks[uniqid()] = [
            'alias' => $blockAlias,
        ];
    }
}
