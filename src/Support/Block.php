<?php

namespace Trinavo\LivewirePageBuilder\Support;

use Illuminate\Support\Str;
use Livewire\Component;
use Trinavo\LivewirePageBuilder\Support\Properties\CheckboxProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ColorProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;

abstract class Block extends Component
{
    public $mobileGridSize = 12;

    public $tabletGridSize = 12;

    public $desktopGridSize = 12;

    public $hiddenMobile = false;

    public $hiddenTablet = false;

    public $hiddenDesktop = false;

    // Padding properties
    public $paddingTop = 0;

    public $paddingRight = 0;

    public $paddingBottom = 0;

    public $paddingLeft = 0;

    // Margin properties
    public $marginTop = 0;

    public $marginRight = 0;

    public $marginBottom = 0;

    public $marginLeft = 0;

    public $textColor = null;

    public $backgroundColor = null;

    // Container properties
    public $useContainer = false;

    // Alignment properties
    public $selfCentered = false;

    public bool $editMode = false;

    public $fullWidth = false;

    /**
     * Get the icon for the block in the page builder UI.
     */
    public function getPageBuilderIcon(): string
    {
        return 'heroicon-o-cube'; // Default icon
    }

    /**
     * Get the label for the block in the page builder UI.
     */
    public function getPageBuilderLabel(): string
    {
        return Str::headline(class_basename(static::class));
    }

    /**
     * Get the shared properties for the block in the page builder UI.
     */
    public function getSharedProperties(): array
    {
        return array_merge(
            $this->getResponsiveProperties(),
            $this->getVisibilityProperties(),
            $this->getSpacingProperties(),
            $this->getStyleProperties(),
            $this->getLayoutProperties()
        );
    }

    /**
     * Get responsive properties (grid sizes)
     */
    protected function getResponsiveProperties(): array
    {
        return [
            (new TextProperty('mobile_grid_size', 'Mobile', numeric: true, defaultValue: 12, min: 1, max: 12))
                ->setGroup('grid_size', 'Grid Size', 3, 'heroicon-o-device-phone-mobile'),
            (new TextProperty('tablet_grid_size', 'Tablet', numeric: true, defaultValue: 12, min: 1, max: 12))
                ->setGroup('grid_size', 'Grid Size', 3, 'heroicon-o-device-tablet'),
            (new TextProperty('desktop_grid_size', 'Desktop', numeric: true, defaultValue: 12, min: 1, max: 12))
                ->setGroup('grid_size', 'Grid Size', 3, 'heroicon-o-device-desktop'),
        ];
    }

    /**
     * Get visibility properties
     */
    protected function getVisibilityProperties(): array
    {
        return [
            (new CheckboxProperty('hidden_mobile', 'Mobile', defaultValue: false))
                ->setGroup('hide', 'Hide', 3, 'heroicon-o-eye'),
            (new CheckboxProperty('hidden_tablet', 'Tablet', defaultValue: false))
                ->setGroup('hide', 'Hide', 3, 'heroicon-o-eye'),
            (new CheckboxProperty('hidden_desktop', 'Desktop', defaultValue: false))
                ->setGroup('hide', 'Hide', 3, 'heroicon-o-eye'),
        ];
    }

    /**
     * Get spacing properties (padding and margin)
     */
    protected function getSpacingProperties(): array
    {
        return [
            // Padding properties
            (new TextProperty('padding_top', 'Top', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('padding', 'Padding', 4, 'heroicon-o-square-2-stack'),
            (new TextProperty('padding_right', 'Right', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('padding', 'Padding', 4, 'heroicon-o-square-2-stack'),
            (new TextProperty('padding_bottom', 'Bottom', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('padding', 'Padding', 4, 'heroicon-o-square-2-stack'),
            (new TextProperty('padding_left', 'Left', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('padding', 'Padding', 4, 'heroicon-o-square-2-stack'),

            // Margin properties
            (new TextProperty('margin_top', 'Top', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('margin', 'Margin', 4, 'heroicon-o-arrows-pointing-out'),
            (new TextProperty('margin_right', 'Right', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('margin', 'Margin', 4, 'heroicon-o-arrows-pointing-out'),
            (new TextProperty('margin_bottom', 'Bottom', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('margin', 'Margin', 4, 'heroicon-o-arrows-pointing-out'),
            (new TextProperty('margin_left', 'Left', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('margin', 'Margin', 4, 'heroicon-o-arrows-pointing-out'),
        ];
    }

    /**
     * Get style properties (colors)
     */
    protected function getStyleProperties(): array
    {
        return [
            (new CheckboxProperty('full_width', 'Full Width', defaultValue: false))
                ->setGroup('width', 'Width', 2, 'heroicon-o-swatch'),
            (new ColorProperty('text_color', 'Text Color', defaultValue: null))
                ->setGroup('color', 'Color', 2, 'heroicon-o-swatch'),
            (new ColorProperty('background_color', 'Background Color', defaultValue: null))
                ->setGroup('color', 'Color', 2, 'heroicon-o-swatch'),
        ];
    }

    /**
     * Get layout properties (container, alignment)
     */
    protected function getLayoutProperties(): array
    {
        return [
            (new CheckboxProperty('use_container', 'Container', defaultValue: false))
                ->setGroup('layout', 'Layout', 2, 'heroicon-o-rectangle-group'),
            (new CheckboxProperty('self_centered', 'Self-centered (mx-auto)', defaultValue: false))
                ->setGroup('layout', 'Layout', 2, 'heroicon-o-rectangle-group'),
        ];
    }

    /**
     * Generate spacing CSS classes based on properties
     */
    public function getSpacingClasses(): string
    {
        $classes = [];

        // Add padding classes
        if ($this->paddingTop > 0) {
            $classes[] = "pt-{$this->paddingTop}";
        }
        if ($this->paddingRight > 0) {
            $classes[] = "pr-{$this->paddingRight}";
        }
        if ($this->paddingBottom > 0) {
            $classes[] = "pb-{$this->paddingBottom}";
        }
        if ($this->paddingLeft > 0) {
            $classes[] = "pl-{$this->paddingLeft}";
        }

        // Add margin classes
        if ($this->marginTop > 0) {
            $classes[] = "mt-{$this->marginTop}";
        }
        if ($this->marginRight > 0) {
            $classes[] = "mr-{$this->marginRight}";
        }
        if ($this->marginBottom > 0) {
            $classes[] = "mb-{$this->marginBottom}";
        }
        if ($this->marginLeft > 0) {
            $classes[] = "ml-{$this->marginLeft}";
        }

        return implode(' ', $classes);
    }

    /**
     * Generate layout CSS classes based on properties
     */
    public function getLayoutClasses(): string
    {
        $classes = [];

        // Add container class if enabled
        if ($this->useContainer) {
            $classes[] = 'container';
        }

        // Add self-centering (mx-auto) if enabled
        if ($this->selfCentered) {
            $classes[] = 'mx-auto';
        }

        return implode(' ', $classes);
    }

    /**
     * Child classes should override this to provide custom properties.
     */
    public function getPageBuilderProperties(): array
    {
        return [];
    }

    public function getPropertyValues(): array
    {
        $propertyValues = [];
        foreach ($this->getSharedProperties() as $property) {
            if ($property->defaultValue) {
                $propertyValues[$property->name] = $property->defaultValue;
            }
        }
        foreach ($this->getPageBuilderProperties() as $property) {
            if ($property->defaultValue) {
                $propertyValues[$property->name] = $property->defaultValue;
            }
        }

        return $propertyValues;
    }

    public function getAllProperties(): array
    {
        return array_merge(
            $this->getSharedProperties(),
            $this->getPageBuilderProperties()
        );
    }
}
