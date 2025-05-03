<?php

namespace Trinavo\LivewirePageBuilder\Support;

use Illuminate\Support\Str;
use Livewire\Component;
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;

abstract class Block extends Component
{
    public $mobileGridSize = 12;

    public $tabletGridSize = 12;

    public $desktopGridSize = 12;

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
            new TextProperty('mobile_grid_size', 'Mobile Grid Size', defaultValue: 12, min: 1, max: 12),
            new TextProperty('tablet_grid_size', 'Tablet Grid Size', defaultValue: 12, min: 1, max: 12),
            new TextProperty('desktop_grid_size', 'Desktop Grid Size', defaultValue: 12, min: 1, max: 12),
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
