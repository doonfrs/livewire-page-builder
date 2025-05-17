<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Attributes\On;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Support\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\SelectProperty;

class RowBlock extends Block
{
    public array $blocks = [];

    public ?string $rowId;

    public ?array $properties;

    public $cssClasses;

    public $inlineStyles;

    public $flex = null;

    public $mobileWidth = 'w-full';

    public $tabletWidth = 'w-full';

    public $desktopWidth = 'w-full';

    public function mount()
    {
        $this->properties = $this->properties ?? $this->getPropertyValues();
        $this->cssClasses = $this->makeClasses();
        $this->inlineStyles = $this->makeInlineStyles();
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
        $this->inlineStyles = $this->makeInlineStyles();
    }

    public function render()
    {
        $properties = $this->properties;
        $properties['editMode'] = $this->editMode;

        $this->flex = $properties['flex'] ?? null;
        if ($this->flex == 'none') {
            $this->flex = null;
        }

        if ($this->editMode) {
            return view('page-builder::livewire.builder.row', [
                'rowId' => $this->rowId,
                'properties' => $properties,
            ]);
        } else {
            return view('page-builder::livewire.builder.row-view', [
                'rowId' => $this->rowId,
                'properties' => $properties,
            ]);
        }
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

    #[On('block-added')]
    public function blockAdded($rowId, $blockId, $blockAlias, $properties, $beforeBlockId = null, $afterBlockId = null)
    {
        if ($rowId != $this->rowId) {
            return;
        }

        if ($beforeBlockId) {
            $block = [
                'alias' => $blockAlias,
                'properties' => $properties,
            ];
            $blockIds = array_keys($this->blocks);
            $position = array_search($beforeBlockId, $blockIds);

            // Create new array in the correct order
            $newBlocks = [];
            foreach ($blockIds as $index => $id) {
                if ($index === $position) {
                    $newBlocks[$blockId] = $block; // Add new block before
                }
                $newBlocks[$id] = $this->blocks[$id]; // Add existing block
            }
            $this->blocks = $newBlocks;
        } elseif ($afterBlockId) {
            $block = [
                'alias' => $blockAlias,
                'properties' => $properties,
            ];
            $blockIds = array_keys($this->blocks);
            $position = array_search($afterBlockId, $blockIds);

            // Create new array in the correct order
            $newBlocks = [];
            foreach ($blockIds as $index => $id) {
                $newBlocks[$id] = $this->blocks[$id]; // Add existing block
                if ($index === $position) {
                    $newBlocks[$blockId] = $block; // Add new block after
                }
            }
            $this->blocks = $newBlocks;
        } else {
            $this->blocks[$blockId] = [
                'alias' => $blockAlias,
                'properties' => $properties,
            ];
        }
    }

    #[On('deleteBlock')]
    public function deleteBlock($blockId)
    {
        if (isset($this->blocks[$blockId])) {
            unset($this->blocks[$blockId]);
        }
    }

    public function makeClasses(): string
    {
        $classString = app(PageBuilderService::class)->getCssClassesFromProperties($this->properties, isRowBlock: true);

        return $classString;
    }

    public function makeInlineStyles(): string
    {
        $styleString = app(PageBuilderService::class)->getInlineStylesFromProperties($this->properties);

        return $styleString;
    }

    #[On('moveBlockUp')]
    public function moveBlockUp($blockId)
    {
        $blockIds = array_keys($this->blocks);
        $currentIndex = array_search($blockId, $blockIds);

        if ($currentIndex > 0) {
            $newOrder = $blockIds;
            $temp = $newOrder[$currentIndex - 1];
            $newOrder[$currentIndex - 1] = $newOrder[$currentIndex];
            $newOrder[$currentIndex] = $temp;

            $this->blocks = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $this->blocks[$id]])->toArray();
        }
    }

    #[On('moveBlockDown')]
    public function moveBlockDown($blockId)
    {
        $blockIds = array_keys($this->blocks);
        $currentIndex = array_search($blockId, $blockIds);
        $lastIndex = count($blockIds) - 1;

        if ($currentIndex !== false && $currentIndex < $lastIndex) {
            $newOrder = $blockIds;
            $temp = $newOrder[$currentIndex + 1];
            $newOrder[$currentIndex + 1] = $newOrder[$currentIndex];
            $newOrder[$currentIndex] = $temp;

            $this->blocks = collect($newOrder)->mapWithKeys(fn ($id) => [$id => $this->blocks[$id]])->toArray();
        }
    }

    public function getPageBuilderProperties(): array
    {
        return [
            new SelectProperty(
                name: 'flex',
                label: 'Flex',
                defaultValue: 'row',
                options: [
                    'none' => 'None',
                    'row' => 'Row',
                    'row-reverse' => 'Row Reverse',
                    'col' => 'Column',
                    'col-reverse' => 'Column Reverse',
                ],
            ),
        ];
    }
}
