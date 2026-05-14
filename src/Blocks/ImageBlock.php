<?php

namespace Trinavo\LivewirePageBuilder\Blocks;

use Trinavo\LivewirePageBuilder\Support\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\CheckboxProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\FlexibleSizeProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ImageProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\SelectProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;

class ImageBlock extends Block
{
    public $image = null;

    public $alt = null;

    public $link = null;

    public $openInNewTab = false;

    public $caption = null;

    public $objectFit = 'cover';

    public $borderRadius = 0;

    public function getPageBuilderLabel(): string
    {
        return __('Image Block');
    }

    public function getPageBuilderCategory(): string
    {
        return __('Content');
    }

    public function getPageBuilderIcon(): string
    {
        return 'pb-icon.image-block';
    }

    public function getPageBuilderProperties(): array
    {
        return [
            ImageProperty::make(name: 'image', label: __('Image')),
            TextProperty::make(name: 'alt', label: __('Alt Text'), defaultValue: $this->alt),
            TextProperty::make(name: 'link', label: __('Link URL'), defaultValue: $this->link),
            new CheckboxProperty('openInNewTab', __('Open in New Tab'), false),
            TextProperty::make(name: 'caption', label: __('Caption'), defaultValue: $this->caption),
            new SelectProperty('objectFit', __('Object Fit'), [
                'cover' => __('Cover'),
                'contain' => __('Contain'),
                'fill' => __('Fill'),
                'none' => __('None'),
            ], $this->objectFit),
            new FlexibleSizeProperty(
                name: 'borderRadius',
                label: __('Border Radius'),
                classes: [],
                allowCustom: true,
                unit: 'px',
                defaultValue: $this->borderRadius
            ),
        ];
    }

    public function render()
    {
        $objectFitClass = match ($this->objectFit) {
            'contain' => 'object-contain',
            'fill' => 'object-fill',
            'none' => 'object-none',
            default => 'object-cover',
        };

        $radiusPx = (int) $this->borderRadius;
        $radiusStyle = $radiusPx > 0 ? " style=\"border-radius: {$radiusPx}px\"" : '';

        if (empty($this->image)) {
            return '<div class="flex items-center justify-center w-full h-40 bg-base-200 dark:bg-base-300 text-base-content/50 rounded-sm">'
                .e(__('No image selected'))
                .'</div>';
        }

        $src = e($this->image);
        $alt = e((string) $this->alt);
        $imgHtml = '<img src="'.$src.'" alt="'.$alt.'" class="w-full h-auto '.$objectFitClass.'"'.$radiusStyle.' />';

        if (! empty($this->link)) {
            $href = e($this->link);
            $target = $this->openInNewTab ? ' target="_blank" rel="noopener noreferrer"' : '';
            $imgHtml = '<a href="'.$href.'"'.$target.'>'.$imgHtml.'</a>';
        }

        $captionHtml = '';
        if (! empty($this->caption)) {
            $captionHtml = '<figcaption class="mt-2 text-sm text-center text-base-content/70">'.e($this->caption).'</figcaption>';
        }

        return '<figure class="w-full">'.$imgHtml.$captionHtml.'</figure>';
    }
}
