<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Attributes\On;
use Trinavo\LivewirePageBuilder\Support\Block;

class RowBlock extends Block
{
    public array $blocks = [];

    public ?string $rowId;

    public ?array $properties;

    public ?bool $viewMode = false;

    public $cssClasses;

    public function mount()
    {
        $this->properties = $this->properties ?? $this->getPropertyValues();
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
        if ($this->viewMode) {
            return view('page-builder::view.row-view', [
                'rowId' => $this->rowId,
                'properties' => $this->properties,
            ]);
        } else {
            return view('page-builder::builder.row', [
                'rowId' => $this->rowId,
                'properties' => $this->properties,
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

    #[On('deleteBlock')]
    public function deleteBlock($blockId)
    {
        if (isset($this->blocks[$blockId])) {
            unset($this->blocks[$blockId]);
        }
    }

    public function makeClasses(): string
    {
        $mobile = $this->properties['mobile_grid_size'] ?? 12;
        $tablet = $this->properties['tablet_grid_size'] ?? 12;
        $desktop = $this->properties['desktop_grid_size'] ?? 12;

        $hiddenMobile = $this->properties['hidden_mobile'] ?? false;
        $hiddenTablet = $this->properties['hidden_tablet'] ?? false;
        $hiddenDesktop = $this->properties['hidden_desktop'] ?? false;

        $classes = [];

        if ($hiddenMobile) {
            $classes[] = 'hidden';
            $classes[] = 'md:hidden';
        }

        if ($hiddenTablet) {
            $classes[] = 'hidden';
            $classes[] = 'lg:hidden';
        }

        if ($hiddenDesktop) {
            $classes[] = 'hidden';
            $classes[] = 'lg:hidden';
        }

        $classes[] = "col-span-$mobile md:col-span-$tablet lg:col-span-$desktop";

        $classes = array_unique($classes);

        return implode(' ', $classes);
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
}
