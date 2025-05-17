<?php

namespace Trinavo\LivewirePageBuilder\Support;

use Illuminate\Support\Str;
use Livewire\Component;
use Trinavo\LivewirePageBuilder\Http\Livewire\BuilderPageBlock;
use Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
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

    public $mobileHeight = null;

    public $tabletHeight = null;

    public $desktopHeight = null;

    public $mobileMinHeight = null;

    public $tabletMinHeight = null;

    public $desktopMinHeight = null;

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
            'w-3xs' => '3xs (256px)',
            'w-2xs' => '2xs (288px)',
            'w-xs' => 'xs (320px)',
            'w-sm' => 'sm (384px)',
            'w-md' => 'md (448px)',
            'w-lg' => 'lg (512px)',
            'w-xl' => 'xl (576px)',
            'w-2xl' => '2xl (672px)',
            'w-3xl' => '3xl (768px)',
            'w-4xl' => '4xl (896px)',
            'w-5xl' => '5xl (1024px)',
            'w-6xl' => '6xl (1152px)',
            'w-7xl' => '7xl (1280px)',
            'w-full' => 'full',
        ];

        if (static::class == RowBlock::class) {
            $defaultValue = 'w-full';
        } elseif (static::class == BuilderPageBlock::class) {
            $defaultValue = 'w-full';
        } else {
            $defaultValue = 'w-auto';
        }

        return [
            (new SelectProperty('mobileWidth', 'Mobile', $widths, defaultValue: $defaultValue))
                ->setGroup('width', 'Width', 3, 'heroicon-o-device-phone-mobile'),
            (new SelectProperty('tabletWidth', 'Tablet', $widths, defaultValue: $defaultValue))
                ->setGroup('width', 'Width', 3, 'heroicon-o-device-tablet'),
            (new SelectProperty('desktopWidth', 'Desktop', $widths, defaultValue: $defaultValue))
                ->setGroup('width', 'Width', 3, 'heroicon-o-device-desktop'),

            (new TextProperty('mobileHeight', 'Mobile', numeric: true, defaultValue: null, min: 0))
                ->setGroup('height', 'Height', 3, 'heroicon-o-device-phone-mobile'),
            (new TextProperty('tabletHeight', 'Tablet', numeric: true, defaultValue: null, min: 0))
                ->setGroup('height', 'Height', 3, 'heroicon-o-device-tablet'),
            (new TextProperty('desktopHeight', 'Desktop', numeric: true, defaultValue: null, min: 0))
                ->setGroup('height', 'Height', 3, 'heroicon-o-device-desktop'),

            (new TextProperty('mobileMinHeight', 'Mobile', numeric: true, defaultValue: null, min: 0))
                ->setGroup('min_height', 'Min Height', 3, 'heroicon-o-device-phone-mobile'),
            (new TextProperty('tabletMinHeight', 'Tablet', numeric: true, defaultValue: null, min: 0))
                ->setGroup('min_height', 'Min Height', 3, 'heroicon-o-device-tablet'),
            (new TextProperty('desktopMinHeight', 'Desktop', numeric: true, defaultValue: null, min: 0))
                ->setGroup('min_height', 'Min Height', 3, 'heroicon-o-device-desktop'),
        ];
    }

    /**
     * Get visibility properties
     */
    protected function getVisibilityProperties(): array
    {
        return [
            (new CheckboxProperty('hiddenMobile', 'Mobile', defaultValue: false))
                ->setGroup('hide', 'Hide', 3, 'heroicon-o-eye'),
            (new CheckboxProperty('hiddenTablet', 'Tablet', defaultValue: false))
                ->setGroup('hide', 'Hide', 3, 'heroicon-o-eye'),
            (new CheckboxProperty('hiddenDesktop', 'Desktop', defaultValue: false))
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
            (new TextProperty('paddingTop', 'Top', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('padding', 'Padding', 4, 'heroicon-o-square-2-stack'),
            (new TextProperty('paddingRight', 'Right', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('padding', 'Padding', 4, 'heroicon-o-square-2-stack'),
            (new TextProperty('paddingBottom', 'Bottom', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('padding', 'Padding', 4, 'heroicon-o-square-2-stack'),
            (new TextProperty('paddingLeft', 'Left', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('padding', 'Padding', 4, 'heroicon-o-square-2-stack'),

            // Margin properties
            (new TextProperty('marginTop', 'Top', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('margin', 'Margin', 4, 'heroicon-o-arrows-pointing-out'),
            (new TextProperty('marginRight', 'Right', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('margin', 'Margin', 4, 'heroicon-o-arrows-pointing-out'),
            (new TextProperty('marginBottom', 'Bottom', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('margin', 'Margin', 4, 'heroicon-o-arrows-pointing-out'),
            (new TextProperty('marginLeft', 'Left', numeric: true, defaultValue: 0, min: 0))
                ->setGroup('margin', 'Margin', 4, 'heroicon-o-arrows-pointing-out'),
        ];
    }

    /**
     * Get style properties (colors)
     */
    protected function getStyleProperties(): array
    {
        return [
            (new ColorProperty('textColor', 'Text Color', defaultValue: null))
                ->setGroup('color', 'Color', 2, 'heroicon-o-swatch'),
            (new ColorProperty('backgroundColor', 'Background Color', defaultValue: null))
                ->setGroup('color', 'Color', 2, 'heroicon-o-swatch'),
        ];
    }

    /**
     * Get background image properties
     */
    protected function getBackgroundImageProperties(): array
    {
        return [
            (new ImageProperty('backgroundImage', 'Image', defaultValue: null))
                ->setGroup('background_image', 'Background Image', 1, 'heroicon-o-photo'),
            (new SelectProperty('backgroundPosition', 'Position', [
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
                ->setGroup('backgroundImageOptions', 'Background Image Options', 2, 'heroicon-o-photo'),
            (new SelectProperty('backgroundSize', 'Size', [
                'cover' => 'Cover',
                'contain' => 'Contain',
                'auto' => 'Auto',
                '100%' => '100%',
            ], defaultValue: 'cover'))
                ->setGroup('backgroundImageOptions', 'Background Image Options', 2, 'heroicon-o-photo'),
            (new SelectProperty('backgroundRepeat', 'Repeat', [
                'no-repeat' => 'No Repeat',
                'repeat' => 'Repeat',
                'repeat-x' => 'Repeat X',
                'repeat-y' => 'Repeat Y',
            ], defaultValue: 'no-repeat'))
                ->setGroup('backgroundImageOptions', 'Background Image Options', 2, 'heroicon-o-photo'),
        ];
    }

    /**
     * Get layout properties (container, alignment)
     */
    protected function getLayoutProperties(): array
    {
        return [
            (new CheckboxProperty('selfCentered', 'Self-centered (mx-auto)', defaultValue: false))
                ->setGroup('layout', 'Layout', 2, 'heroicon-o-rectangle-group'),
        ];
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

    public function getPageBuilderHeightClasses(): string
    {
        $properties = [
            'mobileHeight' => $this->mobileHeight,
            'tabletHeight' => $this->tabletHeight,
            'desktopHeight' => $this->desktopHeight,
            'mobileMinHeight' => $this->mobileMinHeight,
            'tabletMinHeight' => $this->tabletMinHeight,
            'desktopMinHeight' => $this->desktopMinHeight,
        ];

        return app(PageBuilderService::class)->getHeightCssClassesFromProperties($properties);
    }
}
