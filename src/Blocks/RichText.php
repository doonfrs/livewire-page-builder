<?php

namespace Trinavo\LivewirePageBuilder\Blocks;

use Trinavo\LivewirePageBuilder\Support\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\RichTextProperty;

class RichText extends Block
{
    public $content = 'Hello World';

    public function getPageBuilderLabel(): string
    {
        return __('Rich Text');
    }

    public function getPageBuilderIcon(): string
    {
        return 'heroicon-o-document-text';
    }

    public function getPageBuilderProperties(): array
    {
        return [
            new RichTextProperty('content', __('Content'), 'Hello World'),
        ];
    }

    public function render()
    {
        // Use the global namespace helper function to get localized content
        $content = \pb_localize_content($this->content);

        if ($this->editMode) {
            return '<div>
                ' . $content . '
            </div>';
        } else {
            return '<div>
                ' . $content . '
            </div>';
        }
    }
}
