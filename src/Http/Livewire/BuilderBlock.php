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

        if (! $this->editMode) {
            return view('page-builder::view.builder-block-view', [
                'blockAlias' => $this->blockAlias,
                'blockId' => $this->blockId,
                'editMode' => $this->editMode,
                'classExists' => class_exists($blockClass),
            ]);
        } else {
            return view('page-builder::builder.builder-block', [
                'blockAlias' => $this->blockAlias,
                'blockId' => $this->blockId,
                'editMode' => $this->editMode,
                'classExists' => class_exists($blockClass),
            ]);
        }
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
}
