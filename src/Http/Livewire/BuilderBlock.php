<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Support\Block;

class BuilderBlock extends Component
{
    public $blockAlias;
    public $blockId;
    public $properties;

    public function mount()
    {
        $this->properties = app($this->getBlockClass())->getPropertyValues();
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
    }
}
