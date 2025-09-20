<?php

namespace Trinavo\LivewirePageBuilder\Blocks;

use Trinavo\LivewirePageBuilder\Support\Block;

class Spacer extends Block
{
    public $mobileWidth = 'w-full';

    public $tabletWidth = 'w-full';

    public $desktopWidth = 'w-full';

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
        if ($this->editMode) {
            return "<div class='text-gray-400 italic'>
                {{__('Spacer')}}
            </div>";
        } else {
            return '<div>
            </div>';
        }
    }
}
