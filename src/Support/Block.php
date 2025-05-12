<?php

namespace Trinavo\LivewirePageBuilder\Support;

use Illuminate\Support\Str;
use Livewire\Component;
use Trinavo\LivewirePageBuilder\Support\Properties\CheckboxProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ColorProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ImageProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\SelectProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;

abstract class Block extends Component
{
    public $mobileWidth = 'w-auto';

    public $tabletWidth = 'w-auto';

    public $desktopWidth = 'w-auto';

    public $hiddenMobile = false;

    public $hiddenTablet = false;

    public $hiddenDesktop = false;

    // Padding properties
    public $paddingTop = 0;

    public $paddingRight = 0;

    public $paddingBottom = 0;

    public $paddingLeft = 0;

    // Margin properties
    public $marginTop = 0;

    public $marginRight = 0;

    public $marginBottom = 0;

    public $marginLeft = 0;

    public $textColor = null;

    public $backgroundColor = null;

    // Background image properties
    public $backgroundImage = null;

    public $backgroundPosition = 'center';

    public $backgroundSize = 'cover';

    public $backgroundRepeat = 'no-repeat';

    // Container properties
    public $useContainer = false;

    // Alignment properties
    public $selfCentered = false;

    public bool $editMode = false;

    public ?bool $flexMobile = false;

    public ?bool $flexTablet = false;

    public ?bool $flexDesktop = false;

    public ?int $gapMobile = null;

    public ?int $gapTablet = null;

    public ?int $gapDesktop = null;

    /**
     * Get the icon for the block in the page builder UI.
     */
    public function getPageBuilderIcon(): string
    {
        return 'heroicon-o-cube'; // Default icon
    }

    /**
     * Get the label for the block in the page builder UI.
     */
    public function getPageBuilderLabel(): string
    {
        return Str::headline(class_basename(static::class));
    }

    /**
     * Get the shared properties for the block in the page builder UI.
     */
    public function getSharedProperties(): array
    {
        return array_merge(
            $this->getResponsiveProperties(),
            $this->getVisibilityProperties(),
            $this->getSpacingProperties(),
            $this->getStyleProperties(),
            $this->getBackgroundImageProperties(),
            $this->getLayoutProperties()
        );
    }

    /**
     * Get responsive properties (grid sizes)
     */
    protected function getResponsiveProperties(): array
    {
        $widths = [
            'w-auto' => 'Auto',
            'w-1/3' => '1/3',
            'w-2/3' => '2/3',
            'w-1/2' => '1/2',
            'w-1/4' => '1/4',
            'w-3/4' => '3/4',
            'w-1/5' => '1/5',
            'w-2/5' => '2/5',
            'w-3/5' => '3/5',
            'w-4/5' => '4/5',
            'w-full' => 'full',
        ];

        return [
            (new SelectProperty('mobile_width', 'Mobile', $widths, defaultValue: 'w-auto'))
                ->setGroup('width', 'Width', 3, 'heroicon-o-device-phone-mobile'),
            (new SelectProperty('tablet_width', 'Tablet', $widths, defaultValue: 'w-auto'))
                ->setGroup('width', 'Width', 3, 'heroicon-o-device-tablet'),
            (new SelectProperty('desktop_width', 'Desktop', $widths, defaultValue: 'w-auto'))
                ->setGroup('width', 'Width', 3, 'heroicon-o-device-desktop'),
        ];
    }

    /**
     * Get visibility properties
     */
    protected function getVisibilityProperties(): array
    {
        return [
            (new CheckboxProperty('hidden_mobile', 'Mobile', defaultValue: false))
                ->setGroup('hide', 'Hide', 3, 'heroicon-o-eye'),
            (new CheckboxProperty('hidden_tablet', 'Tablet', defaultValue: false))
                ->setGroup('hide', 'Hide', 3, 'heroicon-o-eye'),
            (new CheckboxProperty('hidden_desktop', 'Desktop', defaultValue: false))
                ->setGroup('hide', 'Hide', 3, 'heroicon-o-eye'),
        ];
    }

    /**
     * Get spacing properties (padding and margin)
     */
    protected function getSpacingProperties(): array
    {
        return [
            // Padding properties
            (new TextProperty('padding_top', 'Top', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('padding', 'Padding', 4, 'heroicon-o-square-2-stack'),
            (new TextProperty('padding_right', 'Right', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('padding', 'Padding', 4, 'heroicon-o-square-2-stack'),
            (new TextProperty('padding_bottom', 'Bottom', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('padding', 'Padding', 4, 'heroicon-o-square-2-stack'),
            (new TextProperty('padding_left', 'Left', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('padding', 'Padding', 4, 'heroicon-o-square-2-stack'),

            // Margin properties
            (new TextProperty('margin_top', 'Top', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('margin', 'Margin', 4, 'heroicon-o-arrows-pointing-out'),
            (new TextProperty('margin_right', 'Right', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('margin', 'Margin', 4, 'heroicon-o-arrows-pointing-out'),
            (new TextProperty('margin_bottom', 'Bottom', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('margin', 'Margin', 4, 'heroicon-o-arrows-pointing-out'),
            (new TextProperty('margin_left', 'Left', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('margin', 'Margin', 4, 'heroicon-o-arrows-pointing-out'),
        ];
    }

    /**
     * Get style properties (colors)
     */
    protected function getStyleProperties(): array
    {
        return [
            (new ColorProperty('text_color', 'Text Color', defaultValue: null))
                ->setGroup('color', 'Color', 2, 'heroicon-o-swatch'),
            (new ColorProperty('background_color', 'Background Color', defaultValue: null))
                ->setGroup('color', 'Color', 2, 'heroicon-o-swatch'),
        ];
    }

    /**
     * Get background image properties
     */
    protected function getBackgroundImageProperties(): array
    {
        return [
            (new ImageProperty('background_image', 'Image', defaultValue: null))
                ->setGroup('background_image', 'Background Image', 1, 'heroicon-o-photo'),
            (new SelectProperty('background_position', 'Position', [
                'center' => 'Center',
                'top' => 'Top',
                'right' => 'Right',
                'bottom' => 'Bottom',
                'left' => 'Left',
                'top-left' => 'Top Left',
                'top-right' => 'Top Right',
                'bottom-left' => 'Bottom Left',
                'bottom-right' => 'Bottom Right',
            ], defaultValue: 'center'))
                ->setGroup('background_image-options', 'Background Image Options', 2, 'heroicon-o-photo'),
            (new SelectProperty('background_size', 'Size', [
                'cover' => 'Cover',
                'contain' => 'Contain',
                'auto' => 'Auto',
                '100%' => '100%',
            ], defaultValue: 'cover'))
                ->setGroup('background_image-options', 'Background Image Options', 2, 'heroicon-o-photo'),
            (new SelectProperty('background_repeat', 'Repeat', [
                'no-repeat' => 'No Repeat',
                'repeat' => 'Repeat',
                'repeat-x' => 'Repeat X',
                'repeat-y' => 'Repeat Y',
            ], defaultValue: 'no-repeat'))
                ->setGroup('background_image-options', 'Background Image Options', 2, 'heroicon-o-photo'),
        ];
    }

    /**
     * Get layout properties (container, alignment)
     */
    protected function getLayoutProperties(): array
    {
        return [
            (new CheckboxProperty('use_container', 'Container', defaultValue: false))
                ->setGroup('layout', 'Layout', 2, 'heroicon-o-rectangle-group'),
            (new CheckboxProperty('self_centered', 'Self-centered (mx-auto)', defaultValue: false))
                ->setGroup('layout', 'Layout', 2, 'heroicon-o-rectangle-group'),
        ];
    }

    /**
     * Generate spacing CSS classes based on properties
     */
    public function getSpacingClasses(): string
    {
        $classes = [];

        // Add padding classes
        if ($this->paddingTop > 0) {
            $classes[] = "pt-{$this->paddingTop}";
        }
        if ($this->paddingRight > 0) {
            $classes[] = "pr-{$this->paddingRight}";
        }
        if ($this->paddingBottom > 0) {
            $classes[] = "pb-{$this->paddingBottom}";
        }
        if ($this->paddingLeft > 0) {
            $classes[] = "pl-{$this->paddingLeft}";
        }

        // Add margin classes
        if ($this->marginTop > 0) {
            $classes[] = "mt-{$this->marginTop}";
        }
        if ($this->marginRight > 0) {
            $classes[] = "mr-{$this->marginRight}";
        }
        if ($this->marginBottom > 0) {
            $classes[] = "mb-{$this->marginBottom}";
        }
        if ($this->marginLeft > 0) {
            $classes[] = "ml-{$this->marginLeft}";
        }

        return implode(' ', $classes);
    }

    /**
     * Generate layout CSS classes based on properties
     */
    public function getLayoutClasses(): string
    {
        $classes = [];

        // Add container class if enabled
        if ($this->useContainer) {
            $classes[] = 'container';
        }

        // Add self-centering (mx-auto) if enabled
        if ($this->selfCentered) {
            $classes[] = 'mx-auto';
        }

        return implode(' ', $classes);
    }

    /**
     * Generate background image style based on properties
     */
    public function getBackgroundImageStyle(): string
    {
        if (empty($this->backgroundImage)) {
            return '';
        }

        return "background-image: url('{$this->backgroundImage}'); 
                background-position: {$this->backgroundPosition}; 
                background-size: {$this->backgroundSize}; 
                background-repeat: {$this->backgroundRepeat};";
    }

    /**
     * Child classes should override this to provide custom properties.
     */
    public function getPageBuilderProperties(): array
    {
        return [];
    }

    public function getPropertyValues(): array
    {
        $propertyValues = [];
        foreach ($this->getSharedProperties() as $property) {
            if ($property->defaultValue) {
                $propertyValues[$property->name] = $property->defaultValue;
            }
        }
        foreach ($this->getPageBuilderProperties() as $property) {
            if ($property->defaultValue) {
                $propertyValues[$property->name] = $property->defaultValue;
            }
        }

        return $propertyValues;
    }

    public function getAllProperties(): array
    {
        return array_merge(
            $this->getSharedProperties(),
            $this->getPageBuilderProperties()
        );
    }
}
