<?php

namespace Trinavo\LivewirePageBuilder\Blocks;

use Trinavo\LivewirePageBuilder\Support\Block;

class Spacer extends Block
{
    public $mobileWidth = 'w-full';

    public $tabletWidth = 'w-full';

    public $desktopWidth = 'w-full';

    public $mobileHeight = 'h-16';

    public $tabletHeight = 'h-16';

    public $desktopHeight = 'h-16';

    public $mobileMinHeight = null;

    public $tabletMinHeight = null;

    public $desktopMinHeight = null;

    public function getPageBuilderLabel(): string
    {
        return __('Spacer');
    }

    public function getPageBuilderIcon(): string
    {
        return 'heroicon-o-rectangle-stack';
    }

    public function render()
    {
        $heightClasses = $this->getPageBuilderHeightClasses();
        if ($this->editMode) {
            return "<div class='flex items-center justify-center text-gray-400 text-sm italic border border-dashed border-gray-300 bg-gray-50/50 w-full min-h-8 {$heightClasses}'>
                ".__('Spacer').'
            </div>';
        } else {
            return "<div class='w-full {$heightClasses}'></div>";
        }
    }
}
