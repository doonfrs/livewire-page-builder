<?php

namespace Trinavo\LivewirePageBuilder\Blocks;

use Trinavo\LivewirePageBuilder\Support\Block;

class Section extends Block
{
    public function getPageBuilderLabel(): string
    {
        return __('Section');
    }

    public function getPageBuilderIcon(): string
    {
        return 'heroicon-o-rectangle-group';
    }

    public function render()
    {
        if ($this->editMode) {
            return "<div class='text-gray-400 italic'>
                {{__('Section')}}
            </div>";
        } else {
            return '<div>
            </div>';
        }
    }
}
