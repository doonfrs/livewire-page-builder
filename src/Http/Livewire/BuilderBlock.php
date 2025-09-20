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

    public ?array $blocks = []; // For nested rows

    public $cssClasses;

    public $inlineStyles;

    public ?bool $editMode = false;

    public function mount()
    {
        $blockClass = $this->getBlockClass();
        if (class_exists($blockClass)) {
            $block = app($blockClass);
            $this->properties = $this->properties ?? $block->getPropertyValues();

            // Apply CSS classes and styles for all block types including RowBlocks
            $this->cssClasses = $this->makeClasses();
            $this->inlineStyles = $this->makeInlineStyles();
        }
    }

    public function render()
    {
        $blockClass = $this->getBlockClass();
        $this->properties['editMode'] = $this->editMode;

        $componentProperties = $this->properties;

        // For RowBlock, add the nested blocks and mark as nested
        if ($blockClass === \Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock::class) {
            $componentProperties['blocks'] = $this->blocks ?? [];
            $componentProperties['rowId'] = $this->blockId; // Use block ID as row ID for nested rows
            $componentProperties['isNested'] = true; // Flag to indicate this is a nested row
        }

        return view('page-builder::livewire.builder.builder-block', [
            'blockAlias' => $this->blockAlias,
            'blockId' => $this->blockId,
            'editMode' => $this->editMode,
            'classExists' => class_exists($blockClass),
            'componentProperties' => $componentProperties,
            'isRowBlock' => $blockClass === \Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock::class,
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
        // Handle nested row property updates (when this BuilderBlock wraps a nested RowBlock)
        if ($rowId && $rowId == $this->blockId && !$blockId) {
            // This is a nested row property update for this BuilderBlock
            $this->properties[$propertyName] = $value;

            // Update CSS classes and styles
            $this->cssClasses = $this->makeClasses();
            $this->inlineStyles = $this->makeInlineStyles();

            // Force re-render to reflect changes
            $this->dispatch('$refresh');
            return;
        }

        // Handle regular block property updates
        if (!$rowId && $blockId == $this->blockId) {
            $this->properties[$propertyName] = $value;

            // Update CSS classes and styles for all block types
            $this->cssClasses = $this->makeClasses();
            $this->inlineStyles = $this->makeInlineStyles();
        }
    }

    public function makeClasses(): string
    {
        // For RowBlocks, apply the sizing properties to the wrapper (this BuilderBlock)
        // since it's the actual flex item in the parent container
        $isRowBlock = $this->getBlockClass() === \Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock::class;

        $classString = app(PageBuilderService::class)->getCssClassesFromProperties($this->properties, isRowBlock: $isRowBlock);

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
