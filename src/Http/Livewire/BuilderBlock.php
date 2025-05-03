<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;

class BuilderBlock extends Component
{
    public $blockAlias;

    public $blockId;

    public $properties;

    public $cssClasses;

    public $mobileColumns = 12;

    public $tabletColumns = 12;

    public $desktopColumns = 12;

    public function mount()
    {
        $block = app($this->getBlockClass());
        $this->properties = $block->getPropertyValues();
        $this->cssClasses = $this->makeClasses();
    }

    public function render()
    {
        return view('page-builder::builder-block', [
            'blockAlias' => $this->blockAlias,
            'blockId' => $this->blockId,
            'properties' => $this->properties,
        ]);
    }

    public function blockSelected()
    {
        $this->dispatch(
            'block-selected',
            blockId: $this->blockId,
            properties: $this->properties,
            blockClass: md5($this->getBlockClass()),
        );
    }

    public function getBlockClass()
    {
        return app(PageBuilderService::class)->getClassNameFromAlias($this->blockAlias);
    }

    #[On('updateBlockProperty')]
    public function updateBlockProperty($rowId, $blockId, $propertyName, $value)
    {
        if ($blockId != $this->blockId) {
            return;
        }
        $this->properties[$propertyName] = $value;
        $this->cssClasses = $this->makeClasses();
    }

    public function makeClasses(): string
    {
        $mobile = $this->properties['mobile_columns'] ?? 12;
        $tablet = $this->properties['tablet_columns'] ?? 12;
        $desktop = $this->properties['desktop_columns'] ?? 12;

        return "col-span-$mobile md:col-span-$tablet lg:col-span-$desktop";
    }
}
