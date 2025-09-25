<?php

namespace Trinavo\LivewirePageBuilder\Support;

use Illuminate\Support\Str;
use Livewire\Component;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Support\Properties\CheckboxProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ColorProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\FlexibleSizeProperty;
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

    // Padding properties - Desktop
    public $desktopPaddingTop = 0;

    public $desktopPaddingRight = 0;

    public $desktopPaddingBottom = 0;

    public $desktopPaddingLeft = 0;

    // Padding properties - Tablet
    public $tabletPaddingTop = 0;

    public $tabletPaddingRight = 0;

    public $tabletPaddingBottom = 0;

    public $tabletPaddingLeft = 0;

    // Padding properties - Mobile
    public $mobilePaddingTop = 0;

    public $mobilePaddingRight = 0;

    public $mobilePaddingBottom = 0;

    public $mobilePaddingLeft = 0;

    // Margin properties - Desktop
    public $desktopMarginTop = 0;

    public $desktopMarginRight = 0;

    public $desktopMarginBottom = 0;

    public $desktopMarginLeft = 0;

    // Margin properties - Tablet
    public $tabletMarginTop = 0;

    public $tabletMarginRight = 0;

    public $tabletMarginBottom = 0;

    public $tabletMarginLeft = 0;

    // Margin properties - Mobile
    public $mobileMarginTop = 0;

    public $mobileMarginRight = 0;

    public $mobileMarginBottom = 0;

    public $mobileMarginLeft = 0;

    public $textColor = null;

    public $backgroundColor = null;

    // Background image properties
    public $backgroundImage = null;

    public $backgroundPosition = 'center';

    public $backgroundSize = 'cover';

    public $backgroundRepeat = 'no-repeat';

    public $selfCentered = false;

    // Position properties
    public $position = null;

    // Border properties
    public $borderWidth = null;

    public $borderTopWidth = null;

    public $borderRightWidth = null;

    public $borderBottomWidth = null;

    public $borderLeftWidth = null;

    public $borderColor = null;

    public $borderTopColor = null;

    public $borderRightColor = null;

    public $borderBottomColor = null;

    public $borderLeftColor = null;

    public $borderRadius = null;

    public $borderTopLeftRadius = null;

    public $borderTopRightRadius = null;

    public $borderBottomRightRadius = null;

    public $borderBottomLeftRadius = null;

    // Box shadow properties
    public $boxShadow = null;

    public $boxShadowColor = null;

    public $boxShadowOffsetX = 0;

    public $boxShadowOffsetY = 0;

    public $boxShadowBlur = 0;

    public $boxShadowSpread = 0;

    public $boxShadowInset = false;

    public bool $editMode = false;

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
            $this->getBorderProperties(),
            $this->getBoxShadowProperties(),
            $this->getLayoutProperties()
        );
    }

    /**
     * Get responsive properties (grid sizes)
     */
    protected function getResponsiveProperties(): array
    {
        $widths = $this->getPageBuilderWidthList();
        $heights = $this->getPageBuilderHeightList();
        $minHeights = $this->getPageBuilderMinHeightList();

        return [
            (new FlexibleSizeProperty('mobileWidth', 'Mobile', $widths, allowCustom: true, unit: 'px', defaultValue: $this->mobileWidth))
                ->setGroup('width', 'Width', 3, 'heroicon-o-device-phone-mobile'),
            (new FlexibleSizeProperty('tabletWidth', 'Tablet', $widths, allowCustom: true, unit: 'px', defaultValue: $this->tabletWidth))
                ->setGroup('width', 'Width', 3, 'heroicon-o-device-tablet'),
            (new FlexibleSizeProperty('desktopWidth', 'Desktop', $widths, allowCustom: true, unit: 'px', defaultValue: $this->desktopWidth))
                ->setGroup('width', 'Width', 3, 'heroicon-o-computer-desktop'),

            (new FlexibleSizeProperty('mobileHeight', 'Mobile', $heights, allowCustom: true, unit: 'px', defaultValue: $this->mobileHeight))
                ->setGroup('height', 'Height', 3, 'heroicon-o-device-phone-mobile'),
            (new FlexibleSizeProperty('tabletHeight', 'Tablet', $heights, allowCustom: true, unit: 'px', defaultValue: $this->tabletHeight))
                ->setGroup('height', 'Height', 3, 'heroicon-o-device-tablet'),
            (new FlexibleSizeProperty('desktopHeight', 'Desktop', $heights, allowCustom: true, unit: 'px', defaultValue: $this->desktopHeight))
                ->setGroup('height', 'Height', 3, 'heroicon-o-computer-desktop'),

            (new FlexibleSizeProperty('mobileMinHeight', 'Mobile', $minHeights, allowCustom: true, unit: 'px', defaultValue: $this->mobileMinHeight))
                ->setGroup('min_height', 'Min Height', 3, 'heroicon-o-device-phone-mobile'),
            (new FlexibleSizeProperty('tabletMinHeight', 'Tablet', $minHeights, allowCustom: true, unit: 'px', defaultValue: $this->tabletMinHeight))
                ->setGroup('min_height', 'Min Height', 3, 'heroicon-o-device-tablet'),
            (new FlexibleSizeProperty('desktopMinHeight', 'Desktop', $minHeights, allowCustom: true, unit: 'px', defaultValue: $this->desktopMinHeight))
                ->setGroup('min_height', 'Min Height', 3, 'heroicon-o-computer-desktop'),
        ];
    }

    /**
     * Get visibility properties
     */
    protected function getVisibilityProperties(): array
    {
        return [
            (new CheckboxProperty('hiddenMobile', 'Mobile', defaultValue: $this->hiddenMobile))
                ->setGroup('hide', 'Hide', 3, 'heroicon-o-eye'),
            (new CheckboxProperty('hiddenTablet', 'Tablet', defaultValue: $this->hiddenTablet))
                ->setGroup('hide', 'Hide', 3, 'heroicon-o-eye'),
            (new CheckboxProperty('hiddenDesktop', 'Desktop', defaultValue: $this->hiddenDesktop))
                ->setGroup('hide', 'Hide', 3, 'heroicon-o-eye'),
        ];
    }

    /**
     * Get spacing properties (padding and margin)
     */
    protected function getSpacingProperties(): array
    {
        return [
            // Desktop Padding
            (new TextProperty('desktopPaddingTop', 'Top', numeric: true, defaultValue: $this->desktopPaddingTop, min: 0))
                ->setGroup('desktop_padding', 'Desktop Padding', 4, 'heroicon-o-computer-desktop'),
            (new TextProperty('desktopPaddingRight', 'Right', numeric: true, defaultValue: $this->desktopPaddingRight, min: 0))
                ->setGroup('desktop_padding', 'Desktop Padding', 4, 'heroicon-o-computer-desktop'),
            (new TextProperty('desktopPaddingBottom', 'Bottom', numeric: true, defaultValue: $this->desktopPaddingBottom, min: 0))
                ->setGroup('desktop_padding', 'Desktop Padding', 4, 'heroicon-o-computer-desktop'),
            (new TextProperty('desktopPaddingLeft', 'Left', numeric: true, defaultValue: $this->desktopPaddingLeft, min: 0))
                ->setGroup('desktop_padding', 'Desktop Padding', 4, 'heroicon-o-computer-desktop'),

            // Tablet Padding
            (new TextProperty('tabletPaddingTop', 'Top', numeric: true, defaultValue: $this->tabletPaddingTop, min: 0))
                ->setGroup('tablet_padding', 'Tablet Padding', 4, 'heroicon-o-device-tablet'),
            (new TextProperty('tabletPaddingRight', 'Right', numeric: true, defaultValue: $this->tabletPaddingRight, min: 0))
                ->setGroup('tablet_padding', 'Tablet Padding', 4, 'heroicon-o-device-tablet'),
            (new TextProperty('tabletPaddingBottom', 'Bottom', numeric: true, defaultValue: $this->tabletPaddingBottom, min: 0))
                ->setGroup('tablet_padding', 'Tablet Padding', 4, 'heroicon-o-device-tablet'),
            (new TextProperty('tabletPaddingLeft', 'Left', numeric: true, defaultValue: $this->tabletPaddingLeft, min: 0))
                ->setGroup('tablet_padding', 'Tablet Padding', 4, 'heroicon-o-device-tablet'),

            // Mobile Padding
            (new TextProperty('mobilePaddingTop', 'Top', numeric: true, defaultValue: $this->mobilePaddingTop, min: 0))
                ->setGroup('mobile_padding', 'Mobile Padding', 4, 'heroicon-o-device-phone-mobile'),
            (new TextProperty('mobilePaddingRight', 'Right', numeric: true, defaultValue: $this->mobilePaddingRight, min: 0))
                ->setGroup('mobile_padding', 'Mobile Padding', 4, 'heroicon-o-device-phone-mobile'),
            (new TextProperty('mobilePaddingBottom', 'Bottom', numeric: true, defaultValue: $this->mobilePaddingBottom, min: 0))
                ->setGroup('mobile_padding', 'Mobile Padding', 4, 'heroicon-o-device-phone-mobile'),
            (new TextProperty('mobilePaddingLeft', 'Left', numeric: true, defaultValue: $this->mobilePaddingLeft, min: 0))
                ->setGroup('mobile_padding', 'Mobile Padding', 4, 'heroicon-o-device-phone-mobile'),

            // Desktop Margin
            (new TextProperty('desktopMarginTop', 'Top', numeric: true, defaultValue: $this->desktopMarginTop, min: 0))
                ->setGroup('desktop_margin', 'Desktop Margin', 4, 'heroicon-o-computer-desktop'),
            (new TextProperty('desktopMarginRight', 'Right', numeric: true, defaultValue: $this->desktopMarginRight, min: 0))
                ->setGroup('desktop_margin', 'Desktop Margin', 4, 'heroicon-o-computer-desktop'),
            (new TextProperty('desktopMarginBottom', 'Bottom', numeric: true, defaultValue: $this->desktopMarginBottom, min: 0))
                ->setGroup('desktop_margin', 'Desktop Margin', 4, 'heroicon-o-computer-desktop'),
            (new TextProperty('desktopMarginLeft', 'Left', numeric: true, defaultValue: $this->desktopMarginLeft, min: 0))
                ->setGroup('desktop_margin', 'Desktop Margin', 4, 'heroicon-o-computer-desktop'),

            // Tablet Margin
            (new TextProperty('tabletMarginTop', 'Top', numeric: true, defaultValue: $this->tabletMarginTop, min: 0))
                ->setGroup('tablet_margin', 'Tablet Margin', 4, 'heroicon-o-device-tablet'),
            (new TextProperty('tabletMarginRight', 'Right', numeric: true, defaultValue: $this->tabletMarginRight, min: 0))
                ->setGroup('tablet_margin', 'Tablet Margin', 4, 'heroicon-o-device-tablet'),
            (new TextProperty('tabletMarginBottom', 'Bottom', numeric: true, defaultValue: $this->tabletMarginBottom, min: 0))
                ->setGroup('tablet_margin', 'Tablet Margin', 4, 'heroicon-o-device-tablet'),
            (new TextProperty('tabletMarginLeft', 'Left', numeric: true, defaultValue: $this->tabletMarginLeft, min: 0))
                ->setGroup('tablet_margin', 'Tablet Margin', 4, 'heroicon-o-device-tablet'),

            // Mobile Margin
            (new TextProperty('mobileMarginTop', 'Top', numeric: true, defaultValue: $this->mobileMarginTop, min: 0))
                ->setGroup('mobile_margin', 'Mobile Margin', 4, 'heroicon-o-device-phone-mobile'),
            (new TextProperty('mobileMarginRight', 'Right', numeric: true, defaultValue: $this->mobileMarginRight, min: 0))
                ->setGroup('mobile_margin', 'Mobile Margin', 4, 'heroicon-o-device-phone-mobile'),
            (new TextProperty('mobileMarginBottom', 'Bottom', numeric: true, defaultValue: $this->mobileMarginBottom, min: 0))
                ->setGroup('mobile_margin', 'Mobile Margin', 4, 'heroicon-o-device-phone-mobile'),
            (new TextProperty('mobileMarginLeft', 'Left', numeric: true, defaultValue: $this->mobileMarginLeft, min: 0))
                ->setGroup('mobile_margin', 'Mobile Margin', 4, 'heroicon-o-device-phone-mobile'),
        ];
    }

    /**
     * Get style properties (colors)
     */
    protected function getStyleProperties(): array
    {
        return [
            (new ColorProperty('textColor', 'Text Color', defaultValue: $this->textColor))
                ->setGroup('color', 'Color', 2, 'heroicon-o-swatch'),
            (new ColorProperty('backgroundColor', 'Background Color', defaultValue: $this->backgroundColor))
                ->setGroup('color', 'Color', 2, 'heroicon-o-swatch'),
        ];
    }

    /**
     * Get background image properties
     */
    protected function getBackgroundImageProperties(): array
    {
        return [
            (new ImageProperty('backgroundImage', 'Image', defaultValue: $this->backgroundImage))
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
            ], defaultValue: $this->backgroundPosition))
                ->setGroup('backgroundImageOptions', 'Background Image Options', 2, 'heroicon-o-photo'),
            (new SelectProperty('backgroundSize', 'Size', [
                'cover' => 'Cover',
                'contain' => 'Contain',
                'auto' => 'Auto',
                '100%' => '100%',
            ], defaultValue: $this->backgroundSize))
                ->setGroup('backgroundImageOptions', 'Background Image Options', 2, 'heroicon-o-photo'),
            (new SelectProperty('backgroundRepeat', 'Repeat', [
                'no-repeat' => 'No Repeat',
                'repeat' => 'Repeat',
                'repeat-x' => 'Repeat X',
                'repeat-y' => 'Repeat Y',
            ], defaultValue: $this->backgroundRepeat))
                ->setGroup('backgroundImageOptions', 'Background Image Options', 2, 'heroicon-o-photo'),
        ];
    }

    /**
     * Get border properties (width, color, radius)
     */
    protected function getBorderProperties(): array
    {
        $borderWidths = $this->getBorderWidthList();
        $borderRadiusOptions = $this->getBorderRadiusList();

        return [
            // Border Width - All sides or individual
            (new SelectProperty('borderTopWidth', 'Top', $borderWidths, defaultValue: $this->borderTopWidth))
                ->setGroup('border_width', 'Border Width', 4, 'heroicon-o-square-3-stack-3d'),
            (new SelectProperty('borderRightWidth', 'Right', $borderWidths, defaultValue: $this->borderRightWidth))
                ->setGroup('border_width', 'Border Width', 4, 'heroicon-o-square-3-stack-3d'),
            (new SelectProperty('borderBottomWidth', 'Bottom', $borderWidths, defaultValue: $this->borderBottomWidth))
                ->setGroup('border_width', 'Border Width', 4, 'heroicon-o-square-3-stack-3d'),
            (new SelectProperty('borderLeftWidth', 'Left', $borderWidths, defaultValue: $this->borderLeftWidth))
                ->setGroup('border_width', 'Border Width', 4, 'heroicon-o-square-3-stack-3d'),
            (new SelectProperty('borderWidth', 'All', $borderWidths, defaultValue: $this->borderWidth))
                ->setGroup('border_width', 'Border Width', 4, 'heroicon-o-square-3-stack-3d'),

            // Border Color - All sides or individual
            (new ColorProperty('borderTopColor', 'Top', defaultValue: $this->borderTopColor))
                ->setGroup('border_color', 'Border Color', 4, 'heroicon-o-swatch'),
            (new ColorProperty('borderRightColor', 'Right', defaultValue: $this->borderRightColor))
                ->setGroup('border_color', 'Border Color', 4, 'heroicon-o-swatch'),
            (new ColorProperty('borderBottomColor', 'Bottom', defaultValue: $this->borderBottomColor))
                ->setGroup('border_color', 'Border Color', 4, 'heroicon-o-swatch'),
            (new ColorProperty('borderLeftColor', 'Left', defaultValue: $this->borderLeftColor))
                ->setGroup('border_color', 'Border Color', 4, 'heroicon-o-swatch'),
            (new ColorProperty('borderColor', 'All', defaultValue: $this->borderColor))
                ->setGroup('border_color', 'Border Color', 4, 'heroicon-o-swatch'),

            // Border Radius - All corners or individual
            (new SelectProperty('borderTopLeftRadius', 'Top Left', $borderRadiusOptions, defaultValue: $this->borderTopLeftRadius))
                ->setGroup('border_radius', 'Border Radius', 4, 'heroicon-o-square-2-stack'),
            (new SelectProperty('borderTopRightRadius', 'Top Right', $borderRadiusOptions, defaultValue: $this->borderTopRightRadius))
                ->setGroup('border_radius', 'Border Radius', 4, 'heroicon-o-square-2-stack'),
            (new SelectProperty('borderBottomRightRadius', 'Bottom Right', $borderRadiusOptions, defaultValue: $this->borderBottomRightRadius))
                ->setGroup('border_radius', 'Border Radius', 4, 'heroicon-o-square-2-stack'),
            (new SelectProperty('borderBottomLeftRadius', 'Bottom Left', $borderRadiusOptions, defaultValue: $this->borderBottomLeftRadius))
                ->setGroup('border_radius', 'Border Radius', 4, 'heroicon-o-square-2-stack'),
            (new SelectProperty('borderRadius', 'All', $borderRadiusOptions, defaultValue: $this->borderRadius))
                ->setGroup('border_radius', 'Border Radius', 4, 'heroicon-o-square-2-stack'),

        ];
    }

    /**
     * Get box shadow properties
     */
    protected function getBoxShadowProperties(): array
    {
        $boxShadowOptions = $this->getBoxShadowList();

        return [
            // Shadow Basic Settings (3 items)
            (new SelectProperty('boxShadow', 'Preset', $boxShadowOptions, defaultValue: $this->boxShadow))
                ->setGroup('shadow_basic', 'Shadow Basic', 3, 'heroicon-o-sun'),
            (new ColorProperty('boxShadowColor', 'Color', defaultValue: $this->boxShadowColor))
                ->setGroup('shadow_basic', 'Shadow Basic', 3, 'heroicon-o-sun'),
            (new TextProperty('boxShadowBlur', 'Blur', numeric: true, defaultValue: $this->boxShadowBlur, min: 0, max: 100))
                ->setGroup('shadow_basic', 'Shadow Basic', 3, 'heroicon-o-sun'),

            // Shadow Position (4 items)
            (new TextProperty('boxShadowOffsetX', 'Offset X', numeric: true, defaultValue: $this->boxShadowOffsetX, min: -50, max: 50))
                ->setGroup('shadow_position', 'Shadow Position', 4, 'heroicon-o-arrows-pointing-out'),
            (new TextProperty('boxShadowOffsetY', 'Offset Y', numeric: true, defaultValue: $this->boxShadowOffsetY, min: -50, max: 50))
                ->setGroup('shadow_position', 'Shadow Position', 4, 'heroicon-o-arrows-pointing-out'),
            (new TextProperty('boxShadowSpread', 'Spread', numeric: true, defaultValue: $this->boxShadowSpread, min: -50, max: 50))
                ->setGroup('shadow_position', 'Shadow Position', 4, 'heroicon-o-arrows-pointing-out'),
            (new CheckboxProperty('boxShadowInset', 'Inset', defaultValue: $this->boxShadowInset))
                ->setGroup('shadow_position', 'Shadow Position', 4, 'heroicon-o-arrows-pointing-out'),
        ];
    }

    /**
     * Get layout properties (container, alignment)
     */
    protected function getLayoutProperties(): array
    {
        return [
            (new CheckboxProperty('selfCentered', 'Self Centered', defaultValue: $this->selfCentered))
                ->setGroup('layout', 'Layout', 2, 'heroicon-o-rectangle-group'),
            (new SelectProperty('position', 'Position', [
                '' => 'Static (Default)',
                'relative' => 'Relative',
                'absolute' => 'Absolute',
                'fixed' => 'Fixed',
                'sticky' => 'Sticky',
            ], defaultValue: $this->position))
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
            if ($property->defaultValue !== null) {
                $propertyValues[$property->name] = $property->defaultValue;
            }
        }
        foreach ($this->getPageBuilderProperties() as $property) {
            if ($property->defaultValue !== null) {
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

    public function getPageBuilderSpacingClasses(): string
    {
        $properties = [
            // Device-specific padding
            'mobilePaddingTop' => $this->mobilePaddingTop,
            'mobilePaddingRight' => $this->mobilePaddingRight,
            'mobilePaddingBottom' => $this->mobilePaddingBottom,
            'mobilePaddingLeft' => $this->mobilePaddingLeft,
            'tabletPaddingTop' => $this->tabletPaddingTop,
            'tabletPaddingRight' => $this->tabletPaddingRight,
            'tabletPaddingBottom' => $this->tabletPaddingBottom,
            'tabletPaddingLeft' => $this->tabletPaddingLeft,
            'desktopPaddingTop' => $this->desktopPaddingTop,
            'desktopPaddingRight' => $this->desktopPaddingRight,
            'desktopPaddingBottom' => $this->desktopPaddingBottom,
            'desktopPaddingLeft' => $this->desktopPaddingLeft,
            // Device-specific margin
            'mobileMarginTop' => $this->mobileMarginTop,
            'mobileMarginRight' => $this->mobileMarginRight,
            'mobileMarginBottom' => $this->mobileMarginBottom,
            'mobileMarginLeft' => $this->mobileMarginLeft,
            'tabletMarginTop' => $this->tabletMarginTop,
            'tabletMarginRight' => $this->tabletMarginRight,
            'tabletMarginBottom' => $this->tabletMarginBottom,
            'tabletMarginLeft' => $this->tabletMarginLeft,
            'desktopMarginTop' => $this->desktopMarginTop,
            'desktopMarginRight' => $this->desktopMarginRight,
            'desktopMarginBottom' => $this->desktopMarginBottom,
            'desktopMarginLeft' => $this->desktopMarginLeft,
        ];

        $service = app(PageBuilderService::class);

        return $service->getCssClassesFromProperties($properties);
    }

    public function getPageBuilderWidthList(): array
    {
        return [
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
    }

    public function getPageBuilderHeightList(): array
    {
        return [
            'h-auto' => 'Auto',
            'h-px' => '1px',
            'h-0' => '0px',
            'h-0.5' => '0.125rem',
            'h-1' => '0.25rem',
            'h-1.5' => '0.375rem',
            'h-2' => '0.5rem',
            'h-2.5' => '0.625rem',
            'h-3' => '0.75rem',
            'h-3.5' => '0.875rem',
            'h-4' => '1rem',
            'h-5' => '1.25rem',
            'h-6' => '1.5rem',
            'h-7' => '1.75rem',
            'h-8' => '2rem',
            'h-9' => '2.25rem',
            'h-10' => '2.5rem',
            'h-11' => '2.75rem',
            'h-12' => '3rem',
            'h-14' => '3.5rem',
            'h-16' => '4rem',
            'h-20' => '5rem',
            'h-24' => '6rem',
            'h-28' => '7rem',
            'h-32' => '8rem',
            'h-36' => '9rem',
            'h-40' => '10rem',
            'h-44' => '11rem',
            'h-48' => '12rem',
            'h-52' => '13rem',
            'h-56' => '14rem',
            'h-60' => '15rem',
            'h-64' => '16rem',
            'h-72' => '18rem',
            'h-80' => '20rem',
            'h-96' => '24rem',
            'h-fit' => 'Fit Content',
            'h-min' => 'Min Content',
            'h-max' => 'Max Content',
            'h-full' => 'Full',
            'h-screen' => 'Screen',
            'h-svh' => 'Small Viewport',
            'h-lvh' => 'Large Viewport',
            'h-dvh' => 'Dynamic Viewport',
            'h-1/2' => '50%',
            'h-1/3' => '33.333333%',
            'h-2/3' => '66.666667%',
            'h-1/4' => '25%',
            'h-2/4' => '50%',
            'h-3/4' => '75%',
            'h-1/5' => '20%',
            'h-2/5' => '40%',
            'h-3/5' => '60%',
            'h-4/5' => '80%',
            'h-1/6' => '16.666667%',
            'h-2/6' => '33.333333%',
            'h-3/6' => '50%',
            'h-4/6' => '66.666667%',
            'h-5/6' => '83.333333%',
        ];
    }

    public function getPageBuilderMinHeightList(): array
    {
        return [
            'min-h-0' => '0px',
            'min-h-px' => '1px',
            'min-h-0.5' => '0.125rem',
            'min-h-1' => '0.25rem',
            'min-h-1.5' => '0.375rem',
            'min-h-2' => '0.5rem',
            'min-h-2.5' => '0.625rem',
            'min-h-3' => '0.75rem',
            'min-h-3.5' => '0.875rem',
            'min-h-4' => '1rem',
            'min-h-5' => '1.25rem',
            'min-h-6' => '1.5rem',
            'min-h-7' => '1.75rem',
            'min-h-8' => '2rem',
            'min-h-9' => '2.25rem',
            'min-h-10' => '2.5rem',
            'min-h-11' => '2.75rem',
            'min-h-12' => '3rem',
            'min-h-14' => '3.5rem',
            'min-h-16' => '4rem',
            'min-h-20' => '5rem',
            'min-h-24' => '6rem',
            'min-h-28' => '7rem',
            'min-h-32' => '8rem',
            'min-h-36' => '9rem',
            'min-h-40' => '10rem',
            'min-h-44' => '11rem',
            'min-h-48' => '12rem',
            'min-h-52' => '13rem',
            'min-h-56' => '14rem',
            'min-h-60' => '15rem',
            'min-h-64' => '16rem',
            'min-h-72' => '18rem',
            'min-h-80' => '20rem',
            'min-h-96' => '24rem',
            'min-h-fit' => 'Fit Content',
            'min-h-min' => 'Min Content',
            'min-h-max' => 'Max Content',
            'min-h-full' => 'Full',
            'min-h-screen' => 'Screen',
            'min-h-svh' => 'Small Viewport',
            'min-h-lvh' => 'Large Viewport',
            'min-h-dvh' => 'Dynamic Viewport',
        ];
    }

    public function getBorderWidthList(): array
    {
        return [
            '' => 'None',
            'border-0' => '0px',
            'border' => '1px',
            'border-2' => '2px',
            'border-4' => '4px',
            'border-8' => '8px',
        ];
    }

    public function getBorderRadiusList(): array
    {
        return [
            '' => 'None',
            'rounded-none' => 'None',
            'rounded-sm' => 'Small',
            'rounded' => 'Default',
            'rounded-md' => 'Medium',
            'rounded-lg' => 'Large',
            'rounded-xl' => 'Extra Large',
            'rounded-2xl' => '2X Large',
            'rounded-3xl' => '3X Large',
            'rounded-full' => 'Full',
        ];
    }

    public function getBoxShadowList(): array
    {
        return [
            '' => 'None',
            'shadow-sm' => 'Small',
            'shadow' => 'Default',
            'shadow-md' => 'Medium',
            'shadow-lg' => 'Large',
            'shadow-xl' => 'Extra Large',
            'shadow-2xl' => '2X Large',
            'shadow-inner' => 'Inner',
            'shadow-none' => 'None',
        ];
    }

    /**
     * Convert the block to a JSON serializable array.
     * This can be used for copying/pasting blocks.
     */
    public function toJson(): string
    {
        $properties = $this->getPropertyValues();

        return json_encode([
            'type' => class_basename(static::class),
            'class' => get_class($this),
            'properties' => $properties,
        ]);
    }

    /**
     * Get copy data for the block.
     */
    public function getCopyData(): array
    {
        return [
            'type' => class_basename(static::class),
            'class' => get_class($this),
            'properties' => $this->getPropertyValues(),
        ];
    }
}
