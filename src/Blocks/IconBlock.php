<?php

namespace Trinavo\LivewirePageBuilder\Blocks;

use BladeUI\Icons\Factory;
use Trinavo\LivewirePageBuilder\Support\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\ColorProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\IconProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\RichTextProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\SelectProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;

class IconBlock extends Block
{
    public $icon = 'heroicon-o-star';

    public $label = 'Icon';

    public $cardStyle = 'flat';

    public $buttonText = null;

    public $buttonUrl = null;

    public $buttonBgColor = null;

    public $buttonTextColor = null;

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
            new SelectProperty('cardStyle', __('Card Style'), [
                'card' => __('Card'),
                'flat' => __('Flat'),
            ], $this->cardStyle),
            new RichTextProperty('buttonText', __('Button Text'), is_array($this->buttonText) ? ($this->buttonText['values'] ?? []) : $this->buttonText),
            TextProperty::make(name: 'buttonUrl', label: __('Button URL'), defaultValue: $this->buttonUrl),
            new ColorProperty('buttonBgColor', __('Button Background'), $this->buttonBgColor),
            new ColorProperty('buttonTextColor', __('Button Text Color'), $this->buttonTextColor),
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

        $localizedButtonText = strip_tags(\pb_localize_content($this->buttonText));
        $buttonHtml = '';
        if (! empty($localizedButtonText) && ! empty($this->buttonUrl)) {
            $url = e($this->buttonUrl);
            $btnBgColor = $this->parseColorProperty('buttonBgColor');
            $btnTextColor = $this->parseColorProperty('buttonTextColor');
            $hasBtnStyle = ! $btnBgColor->isEmpty() || ! $btnTextColor->isEmpty();
            $btnClasses = 'btn btn-sm mt-3';
            $btnClasses .= $hasBtnStyle ? ' border-0' : ' btn-outline';
            $btnClasses .= $btnBgColor->isEmpty() ? '' : ' '.$btnBgColor->toBgClass();
            $btnClasses .= $btnTextColor->isEmpty() ? '' : ' '.$btnTextColor->toTextClass();
            $btnStyle = trim($btnBgColor->toInlineStyle('background-color').' '.$btnTextColor->toInlineStyle('color'));
            $styleAttr = $btnStyle ? " style=\"{$btnStyle}\"" : '';
            $buttonHtml = '<a href="'.$url.'" class="'.$btnClasses.'"'.$styleAttr.'>'.e($localizedButtonText).'</a>';
        }

        $isCard = $this->cardStyle === 'card';
        $wrapperClasses = 'flex flex-col items-center justify-center p-8';
        if ($isCard) {
            $wrapperClasses .= ' card bg-base-100 shadow-sm rounded-sm';
        }

        return "<div class='{$wrapperClasses}'>
            {$iconHtml}
            {$labelHtml}
            {$buttonHtml}
        </div>";
    }
}
