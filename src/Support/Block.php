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
            new TextProperty('mobile_columns', 'Mobile Columns'),
            new TextProperty('tablet_columns', 'Tablet Columns'),
            new TextProperty('desktop_columns', 'Desktop Columns'),
        ];
    }

    /**
     * Child classes should override this to provide custom properties.
     */
    public function getPageBuilderProperties(): array
    {
        return [];
    }
}
