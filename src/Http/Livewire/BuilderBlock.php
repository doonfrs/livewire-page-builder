<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;

class BuilderBlock extends Component
{
    public $blockAlias;

    public $blockId;

    public $rowId;

    public ?array $properties;

    public $cssClasses;

    public $inlineStyles;

    public ?bool $editMode = false;

    public function mount()
    {
        $blockClass = $this->getBlockClass();
        if (class_exists($blockClass)) {
            $block = app($blockClass);
            $this->properties = $this->properties ?? $block->getPropertyValues();
            $this->cssClasses = $this->makeClasses();
            $this->inlineStyles = $this->makeInlineStyles();
        }
    }

    public function render()
    {
        $blockClass = $this->getBlockClass();
        $this->properties['editMode'] = $this->editMode;

        return view('page-builder::livewire.builder.builder-block', [
            'blockAlias' => $this->blockAlias,
            'blockId' => $this->blockId,
            'editMode' => $this->editMode,
            'classExists' => class_exists($blockClass),
            'blockClassMd5' => md5($blockClass),
        ]);
    }

    #[On('select-block')]
    public function selectBlock($blockId)
    {
        if ($blockId != $this->blockId) {
            return;
        }
        $this->dispatch(
            'block-selected',
            blockId: $this->blockId,
            properties: $this->properties,
            blockClass: md5($this->getBlockClass()),
        );
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
        if ($rowId || $blockId != $this->blockId) {
            return;
        }
        $this->properties[$propertyName] = $value;

        $this->cssClasses = $this->makeClasses();
        $this->inlineStyles = $this->makeInlineStyles();
    }

    public function makeClasses(): string
    {
        $classString = app(PageBuilderService::class)->getCssClassesFromProperties($this->properties, isRowBlock: false);

        return $classString;
    }

    public function makeInlineStyles(): string
    {
        $styleString = app(PageBuilderService::class)->getInlineStylesFromProperties($this->properties);

        return $styleString;
    }

    /**
     * Copy block data to clipboard.
     */
    public function copyBlock()
    {
        $blockClass = $this->getBlockClass();

        $data = [
            'type' => 'Block',
            'blockId' => $this->blockId,
            'blockAlias' => $this->blockAlias,
            'properties' => $this->properties,
        ];

        $jsonData = json_encode($data);

        // Dispatch an event to copy to clipboard via JavaScript
        $this->dispatch('copy-to-clipboard', data: $jsonData);

        // Success notification
        $this->dispatch(
            'notify',
            message: 'Block copied to clipboard',
            type: 'success'
        );
    }
}
