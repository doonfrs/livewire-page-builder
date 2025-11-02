<?php

namespace Trinavo\LivewirePageBuilder\Support;

use Illuminate\Support\Str;
use Livewire\Component;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Support\Properties\CheckboxProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ColorProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\FlexibleSizeProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ImageProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\ResponsiveSpacingProperty;
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

    public $textAlign = null;

    // Position properties
    public $position = null;

    public $zIndex = null;

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

    // Theme properties
    public $forceDarkMode = false;

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
            $this->getTextProperties(),
            $this->getThemeProperties(),
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
        $paddingValues = [
            'desktop' => [
                'top' => $this->desktopPaddingTop,
                'right' => $this->desktopPaddingRight,
                'bottom' => $this->desktopPaddingBottom,
                'left' => $this->desktopPaddingLeft,
            ],
            'tablet' => [
                'top' => $this->tabletPaddingTop,
                'right' => $this->tabletPaddingRight,
                'bottom' => $this->tabletPaddingBottom,
                'left' => $this->tabletPaddingLeft,
            ],
            'mobile' => [
                'top' => $this->mobilePaddingTop,
                'right' => $this->mobilePaddingRight,
                'bottom' => $this->mobilePaddingBottom,
                'left' => $this->mobilePaddingLeft,
            ],
        ];

        $marginValues = [
            'desktop' => [
                'top' => $this->desktopMarginTop,
                'right' => $this->desktopMarginRight,
                'bottom' => $this->desktopMarginBottom,
                'left' => $this->desktopMarginLeft,
            ],
            'tablet' => [
                'top' => $this->tabletMarginTop,
                'right' => $this->tabletMarginRight,
                'bottom' => $this->tabletMarginBottom,
                'left' => $this->tabletMarginLeft,
            ],
            'mobile' => [
                'top' => $this->mobileMarginTop,
                'right' => $this->mobileMarginRight,
                'bottom' => $this->mobileMarginBottom,
                'left' => $this->mobileMarginLeft,
            ],
        ];

        return [
            (new ResponsiveSpacingProperty('padding', 'Padding', $paddingValues))
                ->setGroup('padding', 'Padding', 1, 'heroicon-o-arrows-pointing-out'),
            (new ResponsiveSpacingProperty('margin', 'Margin', $marginValues))
                ->setGroup('margin', 'Margin', 1, 'heroicon-o-arrows-right-left'),
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
     * Get theme properties
     */
    protected function getThemeProperties(): array
    {
        return [
            new CheckboxProperty(name: 'forceDarkMode', label: 'Force Dark Mode', defaultValue: $this->forceDarkMode),
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
                ->setGroup('layout', 'Layout', 3, 'heroicon-o-rectangle-group'),
            (new SelectProperty('position', 'Position', [
                '' => 'Static (Default)',
                'relative' => 'Relative',
                'absolute' => 'Absolute',
                'fixed' => 'Fixed',
                'sticky' => 'Sticky',
            ], defaultValue: $this->position))
                ->setGroup('layout', 'Layout', 3, 'heroicon-o-rectangle-group'),
            (new SelectProperty('zIndex', 'Z-Index', [
                '' => 'Auto (Default)',
                '-z-50' => '-50',
                '-z-40' => '-40',
                '-z-30' => '-30',
                '-z-20' => '-20',
                '-z-10' => '-10',
                'z-0' => '0',
                'z-10' => '10',
                'z-20' => '20',
                'z-30' => '30',
                'z-40' => '40',
                'z-50' => '50',
            ], defaultValue: $this->zIndex))
                ->setGroup('layout', 'Layout', 3, 'heroicon-o-rectangle-group'),
        ];
    }

    /**
     * Get text properties (alignment)
     */
    protected function getTextProperties(): array
    {
        return [
            (new SelectProperty('textAlign', 'Text Align', [
                '' => 'Default',
                'text-left' => 'Left',
                'text-center' => 'Center',
                'text-right' => 'Right',
                'text-justify' => 'Justify',
                'text-start' => 'Start',
                'text-end' => 'End',
            ], defaultValue: $this->textAlign))
                ->setGroup('text', 'Text', 1, 'heroicon-o-language'),
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
            if ($property instanceof ResponsiveSpacingProperty) {
                foreach ($property->getFieldDefaults() as $fieldName => $value) {
                    if ($value !== null) {
                        $propertyValues[$fieldName] = $value;
                    }
                }

                continue;
            }

            if ($property->defaultValue !== null) {
                $propertyValues[$property->name] = $property->defaultValue;
            }
        }
        foreach ($this->getPageBuilderProperties() as $property) {
            if ($property instanceof ResponsiveSpacingProperty) {
                foreach ($property->getFieldDefaults() as $fieldName => $value) {
                    if ($value !== null) {
                        $propertyValues[$fieldName] = $value;
                    }
                }

                continue;
            }

            if ($property->defaultValue !== null) {
                $propertyValues[$property->name] = $property->defaultValue;
            }
        }

        return $propertyValues;
    }

    public function getAllProperties(): array
    {
        return array_merge(
            $this->getPageBuilderProperties(),
            $this->getSharedProperties()
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
            'w-px' => '1px',
            'w-0' => '0px',
            'w-0.5' => '0.125rem',
            'w-1' => '0.25rem',
            'w-1.5' => '0.375rem',
            'w-2' => '0.5rem',
            'w-2.5' => '0.625rem',
            'w-3' => '0.75rem',
            'w-3.5' => '0.875rem',
            'w-4' => '1rem',
            'w-5' => '1.25rem',
            'w-6' => '1.5rem',
            'w-7' => '1.75rem',
            'w-8' => '2rem',
            'w-9' => '2.25rem',
            'w-10' => '2.5rem',
            'w-11' => '2.75rem',
            'w-12' => '3rem',
            'w-14' => '3.5rem',
            'w-16' => '4rem',
            'w-20' => '5rem',
            'w-24' => '6rem',
            'w-28' => '7rem',
            'w-32' => '8rem',
            'w-36' => '9rem',
            'w-40' => '10rem',
            'w-44' => '11rem',
            'w-48' => '12rem',
            'w-52' => '13rem',
            'w-56' => '14rem',
            'w-60' => '15rem',
            'w-64' => '16rem',
            'w-72' => '18rem',
            'w-80' => '20rem',
            'w-96' => '24rem',
            'w-fit' => 'Fit Content',
            'w-min' => 'Min Content',
            'w-max' => 'Max Content',
            'w-full' => 'Full',
            'w-screen' => 'Screen',
            'w-svw' => 'Small Viewport',
            'w-lvw' => 'Large Viewport',
            'w-dvw' => 'Dynamic Viewport',
            'w-1/2' => '50%',
            'w-1/3' => '33.333333%',
            'w-2/3' => '66.666667%',
            'w-1/4' => '25%',
            'w-2/4' => '50%',
            'w-3/4' => '75%',
            'w-1/5' => '20%',
            'w-2/5' => '40%',
            'w-3/5' => '60%',
            'w-4/5' => '80%',
            'w-1/6' => '16.666667%',
            'w-2/6' => '33.333333%',
            'w-3/6' => '50%',
            'w-4/6' => '66.666667%',
            'w-5/6' => '83.333333%',
            'w-1/12' => '8.333333%',
            'w-2/12' => '16.666667%',
            'w-3/12' => '25%',
            'w-4/12' => '33.333333%',
            'w-5/12' => '41.666667%',
            'w-6/12' => '50%',
            'w-7/12' => '58.333333%',
            'w-8/12' => '66.666667%',
            'w-9/12' => '75%',
            'w-10/12' => '83.333333%',
            'w-11/12' => '91.666667%',
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

    /**
     * Parse a color property value and return a ColorData object
     *
     * @param  string|null  $color  The color value to parse
     * @param  string|null  $defaultClass  Default Tailwind class if color is null/empty
     */
    protected function parseColor(?string $color, ?string $defaultClass = null): ColorData
    {
        return ColorData::parse($color, $defaultClass);
    }

    /**
     * Parse a color property by its property name
     *
     * @param  string  $propertyName  The name of the property containing the color value
     * @param  string|null  $defaultClass  Default Tailwind class if color is null/empty
     */
    protected function parseColorProperty(string $propertyName, ?string $defaultClass = null): ColorData
    {
        $color = $this->$propertyName ?? null;

        return $this->parseColor($color, $defaultClass);
    }
}
