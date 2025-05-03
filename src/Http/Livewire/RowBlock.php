<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Attributes\On;
use Trinavo\LivewirePageBuilder\Support\Block;

class RowBlock extends Block
{
    public array $blocks = [];

    public ?string $rowId;

    public $properties = [];

    public $cssClasses;

    public function mount()
    {
        $this->properties = $this->getPropertyValues();
        $this->cssClasses = $this->makeClasses();
    }

    public function openBlockModal()
    {
        $this->dispatch('openBlockModal', $this->rowId)->to('page-editor');
    }

    #[On('updateBlockProperty')]
    public function updateBlockProperty($rowId, $blockId, $propertyName, $value)
    {
        if ($blockId || $rowId != $this->rowId) {
            return;
        }
        $this->properties[$propertyName] = $value;
        $this->cssClasses = $this->makeClasses();
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

    public function moveRowUp()
    {
        $this->dispatch('moveRowUp', $this->rowId)->to('page-editor');
    }

    public function moveRowDown()
    {
        $this->dispatch('moveRowDown', $this->rowId)->to('page-editor');
    }

    #[On('blockAdded')]
    public function blockAdded($rowId, $blockId, $blockAlias)
    {
        if ($rowId != $this->rowId) {
            return;
        }
        $this->blocks[$blockId] = [
            'alias' => $blockAlias,
        ];
    }

    public function makeClasses(): string
    {
        $mobile = $this->properties['mobile_columns'] ?? 12;
        $tablet = $this->properties['tablet_columns'] ?? 12;
        $desktop = $this->properties['desktop_columns'] ?? 12;

        return "col-span-$mobile md:col-span-$tablet lg:col-span-$desktop";
    }
}
