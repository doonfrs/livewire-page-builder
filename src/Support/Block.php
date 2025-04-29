<?php

namespace Trinavo\LivewirePageBuilder\Support;

use Livewire\Component;
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;

abstract class Block extends Component
{

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
        return class_basename(static::class);
    }

    /**
     * Get the shared properties for the block in the page builder UI.
     */
    public function getSharedProperties(): array
    {
        return [
            new TextProperty('mobile_columns', 'Mobile Columns', defaultValue: 12),
            new TextProperty('tablet_columns', 'Tablet Columns', defaultValue: 12),
            new TextProperty('desktop_columns', 'Desktop Columns', defaultValue: 12),
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

    public function makeClasses(): string
    {
        $values = $this->getPropertyValues();
        $mobile = $values['mobile_columns'] ?? 12;
        $tablet = $values['tablet_columns'] ?? 12;
        $desktop = $values['desktop_columns'] ?? 12;
        return "col-span-$mobile md:col-span-$tablet lg:col-span-$desktop";
    }
}
