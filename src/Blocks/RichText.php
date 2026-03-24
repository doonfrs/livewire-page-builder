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

    public function getPageBuilderCategory(): string
    {
        return __('Content');
    }

    public function getPageBuilderIcon(): string
    {
        return 'pb-icon.rich-text';
    }

    public function getPageBuilderProperties(): array
    {
        return [
            new RichTextProperty('content', __('Content'), 'Hello World'),
        ];
    }

    /**
     * Convert Quill editor classes to Tailwind classes
     */
    protected function convertQuillClassesToTailwind(string $html): string
    {
        // Map Quill alignment classes to Tailwind classes
        $alignmentMap = [
            'ql-align-center' => 'text-center',
            'ql-align-right' => 'text-right',
            'ql-align-left' => 'text-left',
            'ql-align-justify' => 'text-justify',
        ];

        // Replace each Quill class with its Tailwind equivalent
        foreach ($alignmentMap as $quillClass => $tailwindClass) {
            // Pattern to match class attribute containing the Quill class
            $html = preg_replace(
                '/class="([^"]*)\b'.$quillClass.'\b([^"]*)"/i',
                'class="$1'.$tailwindClass.'$2"',
                $html
            );
        }

        // Clean up any double spaces in class attributes
        $html = preg_replace('/class="([^"]*)\s+([^"]*)"/i', 'class="$1 $2"', $html);

        // Trim extra spaces in class attributes
        $html = preg_replace_callback(
            '/class="([^"]*)"/i',
            function ($matches) {
                return 'class="'.trim(preg_replace('/\s+/', ' ', $matches[1])).'"';
            },
            $html
        );

        return $html;
    }

    public function render()
    {
        // Use the global namespace helper function to get localized content
        $content = \pb_localize_content($this->content);

        // In edit mode, don't parse variables to make them editable
        if ($this->editMode) {
            $content = $this->convertQuillClassesToTailwind($content);

            return '<div class="prose dark:prose-invert max-w-none">
                '.$content.'
            </div>';
        } else {
            // In view mode, parse variables to replace with actual values
            $content = VariablesParser::parse($content);
            $content = $this->convertQuillClassesToTailwind($content);

            return '<div class="prose dark:prose-invert max-w-none">
                '.$content.'
            </div>';
        }
    }
}
