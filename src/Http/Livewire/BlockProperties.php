<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Support\Properties\BlockProperty;

class BlockProperties extends Component
{
    public $rowId = null;

    public $blockId = null;

    public $properties = [];

    public $blockProperties = [];

    public $blockClass = null;

    public function render()
    {
        return view('page-builder::block-properties', [
            'blockProperties' => $this->blockProperties,
        ]);
    }

    public function updateBlockProperty($rowId, $blockId, $propertyName, $value)
    {
        $this->properties[$propertyName] = $value;
        $this->dispatch('updateBlockProperty', $rowId, $blockId, $propertyName, $value);
        $this->skipRender();
    }

    #[On('row-selected')]
    public function rowSelected($rowId, $properties)
    {
        $this->rowId = $rowId;
        $this->blockId = null;
        $this->properties = $properties;
        $this->blockClass = RowBlock::class;
        $this->blockProperties =
            array_map(function (BlockProperty $property) {
                return $property->toArray();
            }, app(RowBlock::class)->getAllProperties());
    }

    #[On('block-selected')]
    public function blockSelected($blockId, $properties, $blockClass)
    {
        $this->blockId = $blockId;
        $this->rowId = null;
        $this->properties = $properties;
        $this->blockClass = $this->resolveBlockClass($blockClass);
        $this->blockProperties =
            array_map(function (BlockProperty $property) {
                return $property->toArray();
            }, app($this->blockClass)->getAllProperties());
    }

    public function resolveBlockClass($md5Class)
    {
        foreach (app(PageBuilderService::class)->getConfigBlocks() as $blockClass) {
            if (md5($blockClass) === $md5Class) {
                return $blockClass;
            }
        }
    }
}
