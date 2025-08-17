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

    /**
     * Add basic Tailwind 4 text size classes to headings
     */
    protected function addHeadingSizes(string $html): string
    {
        // Map heading levels to basic Tailwind 4 text size classes
        $headingSizes = [
            'h1' => 'text-4xl',
            'h2' => 'text-3xl',
            'h3' => 'text-2xl',
            'h4' => 'text-xl',
            'h5' => 'text-lg',
            'h6' => 'text-base',
        ];

        // Process each heading tag
        foreach ($headingSizes as $tag => $sizeClass) {
            // Add size class to existing class attributes
            $html = preg_replace(
                '/<'.$tag.'([^>]*class\s*=\s*["\']([^"\']*)["\'][^>]*)>/i',
                '<'.$tag.'$1 class="$2 '.$sizeClass.'">',
                $html
            );

            // Add size class to headings without class attributes
            $html = preg_replace(
                '/<'.$tag.'([^>]*)(?!.*class\s*=\s*["\'][^"\']*["\'])([^>]*)>/i',
                '<'.$tag.'$1$2 class="'.$sizeClass.'">',
                $html
            );
        }

        return $html;
    }

    public function render()
    {
        // Use the global namespace helper function to get localized content
        $content = \pb_localize_content($this->content);

        // In edit mode, don't parse variables to make them editable
        if ($this->editMode) {
            $content = $this->addHeadingSizes($content);

            return '<div>
                '.$content.'
            </div>';
        } else {
            // In view mode, parse variables to replace with actual values
            $content = VariablesParser::parse($content);
            $content = $this->addHeadingSizes($content);

            return '<div>
                '.$content.'
            </div>';
        }
    }
}
