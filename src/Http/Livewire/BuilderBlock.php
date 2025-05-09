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

    public $inlineStyles;

    public ?bool $viewMode = false;

    public function mount()
    {
        $blockClass = $this->getBlockClass();
        if (class_exists($blockClass)) {
            $block = app($blockClass);
            $this->properties = $this->properties ?? $block->getPropertyValues();
            $this->cssClasses = $this->makeClasses();
        }

    }

    public function render()
    {
        $blockClass = $this->getBlockClass();

        if (! class_exists($blockClass)) {
            return '<div>Unknown block: '.$this->blockAlias.'</div>';
        }

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

        // Padding properties
        $paddingTop = $this->properties['padding_top'] ?? 0;
        $paddingRight = $this->properties['padding_right'] ?? 0;
        $paddingBottom = $this->properties['padding_bottom'] ?? 0;
        $paddingLeft = $this->properties['padding_left'] ?? 0;

        // Margin properties
        $marginTop = $this->properties['margin_top'] ?? 0;
        $marginRight = $this->properties['margin_right'] ?? 0;
        $marginBottom = $this->properties['margin_bottom'] ?? 0;
        $marginLeft = $this->properties['margin_left'] ?? 0;

        // Style properties
        $maxWidth = $this->properties['max_width'] ?? null;
        $textColor = $this->properties['text_color'] ?? null;
        $backgroundColor = $this->properties['background_color'] ?? null;

        // Layout properties
        $useContainer = $this->properties['use_container'] ?? false;
        $selfCentered = $this->properties['self_centered'] ?? false;

        $classes = [];
        $styles = [];

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

        // Add container class if enabled
        if ($useContainer) {
            $classes[] = 'container';
        }

        // Add self-centering (mx-auto) if enabled
        if ($selfCentered) {
            $classes[] = 'mx-auto';
        }

        // Add padding classes
        if ($paddingTop > 0) {
            $classes[] = "pt-$paddingTop";
        }
        if ($paddingRight > 0) {
            $classes[] = "pr-$paddingRight";
        }
        if ($paddingBottom > 0) {
            $classes[] = "pb-$paddingBottom";
        }
        if ($paddingLeft > 0) {
            $classes[] = "pl-$paddingLeft";
        }

        // Add margin classes
        if ($marginTop > 0) {
            $classes[] = "mt-$marginTop";
        }
        if ($marginRight > 0) {
            $classes[] = "mr-$marginRight";
        }
        if ($marginBottom > 0) {
            $classes[] = "mb-$marginBottom";
        }
        if ($marginLeft > 0) {
            $classes[] = "ml-$marginLeft";
        }

        // Add style classes
        if ($maxWidth) {
            $classes[] = "max-w-$maxWidth";
        }

        // Add text color classes or inline styles for hex colors
        if ($textColor) {
            if (str_starts_with($textColor, '#')) {
                $styles[] = "color: $textColor";
            } else {
                $classes[] = "text-$textColor";
            }
        }

        // Add background color classes or inline styles for hex colors
        if ($backgroundColor) {
            if (str_starts_with($backgroundColor, '#')) {
                $styles[] = "background-color: $backgroundColor";
            } else {
                $classes[] = "bg-$backgroundColor";
            }
        }

        $classString = implode(' ', array_unique($classes));

        // Add style attribute if we have inline styles
        if (! empty($styles)) {
            $styleString = implode('; ', $styles);
            $this->inlineStyles = $styleString;
        } else {
            $this->inlineStyles = null;
        }

        return $classString;
    }
}
