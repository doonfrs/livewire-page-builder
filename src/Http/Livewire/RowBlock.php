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

    public $inlineStyles;

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
    public function blockAdded($rowId, $blockId, $blockAlias, $properties)
    {
        if ($rowId != $this->rowId) {
            return;
        }
        $this->blocks[$blockId] = [
            'alias' => $blockAlias,
            'properties' => $properties,
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

        $classes[] = 'col-span-12';

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
