<?php

namespace Trinavo\LivewirePageBuilder\Support;

use Illuminate\Support\Str;
use Livewire\Component;
use Trinavo\LivewirePageBuilder\Support\Properties\CheckboxProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;

abstract class Block extends Component
{
    public $mobileGridSize = 12;

    public $tabletGridSize = 12;

    public $desktopGridSize = 12;

    public $hiddenMobile = false;

    public $hiddenTablet = false;

    public $hiddenDesktop = false;

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
        return [
            (new TextProperty('mobile_grid_size', 'Mobile', numeric: true, defaultValue: 12, min: 1, max: 12))
                ->setGroup('grid_size', 'Grid Size', 3, 'heroicon-o-squares-2x2'),
            (new TextProperty('tablet_grid_size', 'Tablet', numeric: true, defaultValue: 12, min: 1, max: 12))
                ->setGroup('grid_size', 'Grid Size'),
            (new TextProperty('desktop_grid_size', 'Desktop', numeric: true, defaultValue: 12, min: 1, max: 12))
                ->setGroup('grid_size', 'Grid Size'),
            (new CheckboxProperty('hidden_mobile', 'Mobile', defaultValue: false))
                ->setGroup('hidden', 'Hidden', 3, 'heroicon-o-eye'),
            (new CheckboxProperty('hidden_tablet', 'Tablet', defaultValue: false))
                ->setGroup('hidden', 'Hidden'),
            (new CheckboxProperty('hidden_desktop', 'Desktop', defaultValue: false))
                ->setGroup('hidden', 'Hidden'),
        ];
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
        $all = [];
        foreach ($this->getSharedProperties() as $property) {
            $all[$property->name] = $property;
        }
        foreach ($this->getPageBuilderProperties() as $property) {
            $all[$property->name] = $property;
        }

        return array_values($all);
    }
}
