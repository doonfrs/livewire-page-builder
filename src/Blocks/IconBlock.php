<?php

namespace Trinavo\LivewirePageBuilder\Blocks;

use BladeUI\Icons\Factory;
use Trinavo\LivewirePageBuilder\Support\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\IconProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;

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
        return 'heroicon-o-star';
    }

    public function getPageBuilderProperties(): array
    {
        return [
            IconProperty::make(name: 'icon', label: __('Icon'), styles: ['outline', 'solid', 'mini', 'regular', 'fill'], sets: ['heroicons', 'bootstrap'], defaultValue: 'heroicon-o-star'),
            TextProperty::make(name: 'label', label: __('Label'), defaultValue: 'Icon'),
        ];
    }

    public function render()
    {
        $iconHtml = '';
        $labelHtml = '';

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

        if (! empty($this->label)) {
            $labelHtml = '<div class="mt-4 text-lg font-medium">'.e($this->label).'</div>';
        }

        return "<div class='flex flex-col items-center justify-center p-8'>
            {$iconHtml}
            {$labelHtml}
        </div>";
    }
}
