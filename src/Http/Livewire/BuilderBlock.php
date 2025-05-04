<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;

class BuilderBlock extends Component
{
    public $blockAlias;

    public $blockId;

    public ?array $properties;

    public $cssClasses;

    public ?bool $viewMode = false;

    public function mount()
    {
        $block = app($this->getBlockClass());
        $this->properties = $this->properties ?? $block->getPropertyValues();
        $this->cssClasses = $this->makeClasses();
    }

    public function render()
    {
        if ($this->viewMode) {
            return view('page-builder::view.builder-block-view', [
                'blockAlias' => $this->blockAlias,
                'blockId' => $this->blockId,
                'properties' => $this->properties,
            ]);
        } else {
            return view('page-builder::builder.builder-block', [
                'blockAlias' => $this->blockAlias,
                'blockId' => $this->blockId,
                'properties' => $this->properties,
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

        // Container query classes
        if ($hiddenMobile && $hiddenTablet && $hiddenDesktop) {
            $classes[] = 'hidden';
        } elseif ($hiddenMobile && $hiddenTablet) {
            $classes[] = 'hidden @md:block';
        } elseif ($hiddenMobile && $hiddenDesktop) {
            $classes[] = 'hidden @sm:block @lg:hidden';
        } elseif ($hiddenTablet && $hiddenDesktop) {
            $classes[] = 'block @md:hidden';
        } elseif ($hiddenMobile) {
            $classes[] = 'hidden @sm:block';
        } elseif ($hiddenTablet) {
            $classes[] = 'block @md:hidden @lg:block';
        } elseif ($hiddenDesktop) {
            $classes[] = 'block @lg:hidden';
        } else {
            $classes[] = 'block';
        }

        $classes[] = "col-span-$mobile @md:col-span-$tablet @lg:col-span-$desktop";

        return implode(' ', array_unique($classes));
    }
}
