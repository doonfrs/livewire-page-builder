<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Component;

class BuilderBlock extends Component
{
    public $cols = 4;
    public $blockName = null;
    public $blockId = null;
    public array $blockProperties = [];

    public function mount() {}

    public function blockSelected($blockId)
    {
        $this->dispatch('block-selected', $blockId);
    }

    public function getColClassesProperty()
    {
        $mobile = $this->blockProperties['mobile_columns'] ?? 12;
        $tablet = $this->blockProperties['tablet_columns'] ?? 12;
        $desktop = $this->blockProperties['desktop_columns'] ?? 12;
        return "col-span-$mobile md:col-span-$tablet lg:col-span-$desktop";
    }

    public function render()
    {
        return view('page-builder::builder-block', [
            'blockName' => $this->blockName,
            'blockId' => $this->blockId,
            'blockProperties' => $this->blockProperties,
        ]);
    }
}
