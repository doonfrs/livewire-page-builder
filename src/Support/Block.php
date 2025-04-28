<?php

namespace Trinavo\LivewirePageBuilder\Support;

use Livewire\Component;

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
}
