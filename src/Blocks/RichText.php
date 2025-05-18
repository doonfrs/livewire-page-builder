<?php

namespace Trinavo\LivewirePageBuilder\Blocks;

use Trinavo\LivewirePageBuilder\Support\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\RichTextProperty;
use Trinavo\LivewirePageBuilder\Support\VariablesParser;

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

        // In edit mode, don't parse variables to make them editable
        if ($this->editMode) {
            return '<div>
                '.$content.'
            </div>';
        } else {
            // In view mode, parse variables to replace with actual values
            $content = VariablesParser::parse($content);

            return '<div>
                '.$content.'
            </div>';
        }
    }
}
