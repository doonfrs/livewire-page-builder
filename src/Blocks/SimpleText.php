<?php

namespace Trinavo\LivewirePageBuilder\Blocks;

use Trinavo\LivewirePageBuilder\Support\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\SimpleTextProperty;
use Trinavo\LivewirePageBuilder\Support\VariablesParser;

class SimpleText extends Block
{
    public $content = 'Hello World';

    public function getPageBuilderLabel(): string
    {
        return __('Simple Text');
    }

    public function getPageBuilderCategory(): string
    {
        return __('Content');
    }

    public function getPageBuilderIcon(): string
    {
        return 'pb-icon.simple-text';
    }

    public function getPageBuilderProperties(): array
    {
        return [
            new SimpleTextProperty(name: 'content', label: __('Content'), defaultValue: 'Hello World'),
        ];
    }

    public function render()
    {
        // Use the global namespace helper function to get localized content
        $content = \pb_localize_content($this->content);

        // Escape HTML to prevent XSS since this is plain text
        $content = e($content);

        // In edit mode, don't parse variables to make them editable
        if ($this->editMode) {
            // Convert newlines to <br> tags for display
            $content = nl2br($content);

            return '<span>'.$content.'</span>';
        } else {
            // In view mode, parse variables to replace with actual values
            $content = VariablesParser::parse($content);

            // Convert newlines to <br> tags for display
            $content = nl2br($content);

            return '<span>'.$content.'</span>';
        }
    }
}
