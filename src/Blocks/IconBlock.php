<?php

namespace Trinavo\LivewirePageBuilder\Blocks;

use BladeUI\Icons\Factory;
use Trinavo\LivewirePageBuilder\Support\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\IconProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\RichTextProperty;

class IconBlock extends Block
{
    public $icon = 'heroicon-o-star';

    public $label = 'Icon';

    public function getPageBuilderLabel(): string
    {
        return __('Icon Block');
    }

    public function getPageBuilderCategory(): string
    {
        return __('Content');
    }

    public function getPageBuilderIcon(): string
    {
        return 'pb-icon.icon-block';
    }

    public function getPageBuilderProperties(): array
    {
        return [
            IconProperty::make(name: 'icon', label: __('Icon'), styles: ['outline', 'solid', 'mini', 'regular', 'fill'], sets: ['heroicons', 'bootstrap'], defaultValue: 'heroicon-o-star'),
            new RichTextProperty('label', __('Label'), is_array($this->label) ? ($this->label['values'] ?? []) : $this->label),
        ];
    }

    public function render()
    {
        $iconHtml = '';

        if (! empty($this->icon)) {
            try {
                $iconFactory = app(Factory::class);
                $iconSvg = $iconFactory->svg(name: $this->icon, class: 'w-16 h-16 text-current');
                $iconHtml = $iconSvg->toHtml();
            } catch (\Exception) {
                // Icon not found, show fallback
                $iconHtml = '<div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded"></div>';
            }
        }

        $localizedLabel = \pb_localize_content($this->label);
        $labelHtml = '';
        if (! empty($localizedLabel)) {
            $labelHtml = '<div class="mt-4 text-lg font-medium">'.$localizedLabel.'</div>';
        }

        return "<div class='flex flex-col items-center justify-center p-8'>
            {$iconHtml}
            {$labelHtml}
        </div>";
    }
}
