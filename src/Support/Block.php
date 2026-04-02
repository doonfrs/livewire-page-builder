<?php

namespace Trinavo\LivewirePageBuilder\Support;

use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
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

    public $lazyLoad = 'disabled';

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

    // Background gradient properties
    public $backgroundGradientFrom = null;

    public $backgroundGradientTo = null;

    public $backgroundGradientDirection = 'to-b';

    // Text gradient properties
    public $textGradientFrom = null;

    public $textGradientTo = null;

    public $textGradientDirection = 'to-r';

    // Background image properties
    public $backgroundImage = null;

    public $backgroundPosition = 'center';

    public $backgroundSize = 'cover';

    public $backgroundRepeat = 'no-repeat';

    public $selfCentered = false;

    public $textAlign = null;

    // Font size properties
    public $mobileFontSize = null;

    public $tabletFontSize = null;

    public $desktopFontSize = null;

    // Position properties
    public $position = null;

    public $zIndex = null;

    // Transform properties - Rotate (degrees)
    public $mobileRotate = 0;

    public $tabletRotate = 0;

    public $desktopRotate = 0;

    // Transform properties - Scale (factor, e.g., 1.0 = 100%, 1.5 = 150%)
    public $mobileScale = 1;

    public $tabletScale = 1;

    public $desktopScale = 1;

    // Transform properties - Translate X (pixels or rem)
    public $mobileTranslateX = 0;

    public $tabletTranslateX = 0;

    public $desktopTranslateX = 0;

    // Transform properties - Translate Y (pixels or rem)
    public $mobileTranslateY = 0;

    public $tabletTranslateY = 0;

    public $desktopTranslateY = 0;

    // Transform properties - Skew X (degrees)
    public $mobileSkewX = 0;

    public $tabletSkewX = 0;

    public $desktopSkewX = 0;

    // Transform properties - Skew Y (degrees)
    public $mobileSkewY = 0;

    public $tabletSkewY = 0;

    public $desktopSkewY = 0;

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

    // Filter properties
    public $backdropBlur = null;

    public $blur = null;

    public $dropShadow = null;

    // Theme properties
    public $forceDarkMode = false;

    #[Locked]
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
     * Get the category for the block in the page builder UI.
     */
    public function getPageBuilderCategory(): string
    {
        return '';
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
            $this->getTypographyProperties(),
            $this->getTextProperties(),
            $this->getThemeProperties(),
            $this->getBackgroundImageProperties(),
            $this->getBorderProperties(),
            $this->getBoxShadowProperties(),
            $this->getBackdropFilterProperties(),
            $this->getFilterProperties(),
            $this->getLayoutProperties(),
            $this->getTransformProperties(),
            $this->getPerformanceProperties()
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

    protected function getPerformanceProperties(): array
    {
        return [
            (new SelectProperty('lazyLoad', __('Lazy Load'), [
                'disabled' => __('Disabled'),
                'on' => __('On Scroll'),
                'on-load' => __('On Load'),
            ], $this->lazyLoad))
                ->setGroup('performance', __('Performance'), 1, 'heroicon-o-bolt'),
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
        $gradientDirections = [
            'to-t' => 'To Top',
            'to-tr' => 'To Top Right',
            'to-r' => 'To Right',
            'to-br' => 'To Bottom Right',
            'to-b' => 'To Bottom',
            'to-bl' => 'To Bottom Left',
            'to-l' => 'To Left',
            'to-tl' => 'To Top Left',
        ];

        return [
            (new ColorProperty('textColor', 'Text Color', defaultValue: $this->textColor))
                ->setGroup('color', 'Color', 2, 'heroicon-o-swatch'),
            (new ColorProperty('backgroundColor', 'Background Color', defaultValue: $this->backgroundColor))
                ->setGroup('color', 'Color', 2, 'heroicon-o-swatch'),

            // Background gradient
            (new ColorProperty('backgroundGradientFrom', 'From', defaultValue: $this->backgroundGradientFrom))
                ->setGroup('bg_gradient', 'Background Gradient', 3, 'heroicon-o-paint-brush'),
            (new ColorProperty('backgroundGradientTo', 'To', defaultValue: $this->backgroundGradientTo))
                ->setGroup('bg_gradient', 'Background Gradient', 3, 'heroicon-o-paint-brush'),
            (new SelectProperty('backgroundGradientDirection', 'Direction', $gradientDirections, defaultValue: $this->backgroundGradientDirection))
                ->setGroup('bg_gradient', 'Background Gradient', 3, 'heroicon-o-paint-brush'),

            // Text gradient
            (new ColorProperty('textGradientFrom', 'From', defaultValue: $this->textGradientFrom))
                ->setGroup('text_gradient', 'Text Gradient', 3, 'heroicon-o-paint-brush'),
            (new ColorProperty('textGradientTo', 'To', defaultValue: $this->textGradientTo))
                ->setGroup('text_gradient', 'Text Gradient', 3, 'heroicon-o-paint-brush'),
            (new SelectProperty('textGradientDirection', 'Direction', $gradientDirections, defaultValue: $this->textGradientDirection))
                ->setGroup('text_gradient', 'Text Gradient', 3, 'heroicon-o-paint-brush'),
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
     * Get backdrop filter properties
     */
    protected function getBackdropFilterProperties(): array
    {
        $backdropBlurOptions = $this->getBackdropBlurList();

        return [
            (new SelectProperty('backdropBlur', 'Backdrop Blur', $backdropBlurOptions, defaultValue: $this->backdropBlur))
                ->setGroup('backdrop_filters', 'Backdrop Filters', 1, 'heroicon-o-sparkles'),
        ];
    }

    /**
     * Get filter properties
     */
    protected function getFilterProperties(): array
    {
        $blurOptions = $this->getBlurList();
        $dropShadowOptions = $this->getDropShadowList();

        return [
            (new SelectProperty('blur', 'Blur', $blurOptions, defaultValue: $this->blur))
                ->setGroup('filters', 'Filters', 2, 'heroicon-o-adjustments-horizontal'),
            (new SelectProperty('dropShadow', 'Drop Shadow', $dropShadowOptions, defaultValue: $this->dropShadow))
                ->setGroup('filters', 'Filters', 2, 'heroicon-o-adjustments-horizontal'),
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
     * Get transform properties (rotate, scale, translate, skew)
     */
    protected function getTransformProperties(): array
    {
        return [
            (new TextProperty('mobileRotate', 'Mobile', numeric: true, defaultValue: $this->mobileRotate))
                ->setGroup('rotate', 'Rotate (deg)', 3, 'heroicon-o-arrow-path'),
            (new TextProperty('tabletRotate', 'Tablet', numeric: true, defaultValue: $this->tabletRotate))
                ->setGroup('rotate', 'Rotate (deg)', 3, 'heroicon-o-arrow-path'),
            (new TextProperty('desktopRotate', 'Desktop', numeric: true, defaultValue: $this->desktopRotate))
                ->setGroup('rotate', 'Rotate (deg)', 3, 'heroicon-o-arrow-path'),

            (new TextProperty('mobileScale', 'Mobile', numeric: true, defaultValue: $this->mobileScale))
                ->setGroup('scale', 'Scale', 3, 'heroicon-o-arrows-pointing-out'),
            (new TextProperty('tabletScale', 'Tablet', numeric: true, defaultValue: $this->tabletScale))
                ->setGroup('scale', 'Scale', 3, 'heroicon-o-arrows-pointing-out'),
            (new TextProperty('desktopScale', 'Desktop', numeric: true, defaultValue: $this->desktopScale))
                ->setGroup('scale', 'Scale', 3, 'heroicon-o-arrows-pointing-out'),

            (new TextProperty('mobileTranslateX', 'Mobile', numeric: true, defaultValue: $this->mobileTranslateX))
                ->setGroup('translateX', 'Translate X (px)', 3, 'heroicon-o-arrows-right-left'),
            (new TextProperty('tabletTranslateX', 'Tablet', numeric: true, defaultValue: $this->tabletTranslateX))
                ->setGroup('translateX', 'Translate X (px)', 3, 'heroicon-o-arrows-right-left'),
            (new TextProperty('desktopTranslateX', 'Desktop', numeric: true, defaultValue: $this->desktopTranslateX))
                ->setGroup('translateX', 'Translate X (px)', 3, 'heroicon-o-arrows-right-left'),

            (new TextProperty('mobileTranslateY', 'Mobile', numeric: true, defaultValue: $this->mobileTranslateY))
                ->setGroup('translateY', 'Translate Y (px)', 3, 'heroicon-o-arrows-up-down'),
            (new TextProperty('tabletTranslateY', 'Tablet', numeric: true, defaultValue: $this->tabletTranslateY))
                ->setGroup('translateY', 'Translate Y (px)', 3, 'heroicon-o-arrows-up-down'),
            (new TextProperty('desktopTranslateY', 'Desktop', numeric: true, defaultValue: $this->desktopTranslateY))
                ->setGroup('translateY', 'Translate Y (px)', 3, 'heroicon-o-arrows-up-down'),

            (new TextProperty('mobileSkewX', 'Mobile', numeric: true, defaultValue: $this->mobileSkewX))
                ->setGroup('skewX', 'Skew X (deg)', 3, 'heroicon-o-forward'),
            (new TextProperty('tabletSkewX', 'Tablet', numeric: true, defaultValue: $this->tabletSkewX))
                ->setGroup('skewX', 'Skew X (deg)', 3, 'heroicon-o-forward'),
            (new TextProperty('desktopSkewX', 'Desktop', numeric: true, defaultValue: $this->desktopSkewX))
                ->setGroup('skewX', 'Skew X (deg)', 3, 'heroicon-o-forward'),

            (new TextProperty('mobileSkewY', 'Mobile', numeric: true, defaultValue: $this->mobileSkewY))
                ->setGroup('skewY', 'Skew Y (deg)', 3, 'heroicon-o-backward'),
            (new TextProperty('tabletSkewY', 'Tablet', numeric: true, defaultValue: $this->tabletSkewY))
                ->setGroup('skewY', 'Skew Y (deg)', 3, 'heroicon-o-backward'),
            (new TextProperty('desktopSkewY', 'Desktop', numeric: true, defaultValue: $this->desktopSkewY))
                ->setGroup('skewY', 'Skew Y (deg)', 3, 'heroicon-o-backward'),
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
     * Get typography properties (font size)
     */
    protected function getTypographyProperties(): array
    {
        $fontSizes = $this->getFontSizeList();

        return [
            (new SelectProperty(name: 'mobileFontSize', label: 'Mobile', options: $fontSizes, defaultValue: $this->mobileFontSize))
                ->setGroup(group: 'font', groupLabel: 'Text Size', columns: 3, groupIcon: 'heroicon-o-bars-3-bottom-left'),
            (new SelectProperty(name: 'tabletFontSize', label: 'Tablet', options: $fontSizes, defaultValue: $this->tabletFontSize))
                ->setGroup(group: 'font', groupLabel: 'Text Size', columns: 3, groupIcon: 'heroicon-o-bars-3-bottom-left'),
            (new SelectProperty(name: 'desktopFontSize', label: 'Desktop', options: $fontSizes, defaultValue: $this->desktopFontSize))
                ->setGroup(group: 'font', groupLabel: 'Text Size', columns: 3, groupIcon: 'heroicon-o-bars-3-bottom-left'),
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

            $resolvedDefault = $this->resolvePropertyDefault($property);
            if ($resolvedDefault !== null) {
                $propertyValues[$property->name] = $resolvedDefault;
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

            $resolvedDefault = $this->resolvePropertyDefault($property);
            if ($resolvedDefault !== null) {
                $propertyValues[$property->name] = $resolvedDefault;
            }
        }

        return $propertyValues;
    }

    /**
     * Resolve the default value for a property, including multilingual properties
     * that may have localizedValues set but no defaultValue.
     */
    private function resolvePropertyDefault($property)
    {
        if ($property->defaultValue !== null) {
            return $property->defaultValue;
        }

        // For multilingual properties with localizedValues, build the multilingual content structure
        if (property_exists($property, 'localizedValues') && ! empty($property->localizedValues)) {
            return app(\Trinavo\LivewirePageBuilder\Services\LocalizationService::class)
                ->createMultilingualContent($property->localizedValues);
        }

        return null;
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
            // Common utilities
            'w-auto' => 'Auto',
            'w-full' => 'Full',
            'w-screen' => 'Screen',
            'w-fit' => 'Fit Content',
            'w-min' => 'Min Content',
            'w-max' => 'Max Content',

            // Fractional widths
            'w-1/2' => '1/2',
            'w-1/3' => '1/3',
            'w-2/3' => '2/3',
            'w-1/4' => '1/4',
            'w-3/4' => '3/4',
            'w-1/5' => '1/5',
            'w-2/5' => '2/5',
            'w-3/5' => '3/5',
            'w-4/5' => '4/5',
            'w-1/6' => '1/6',
            'w-5/6' => '5/6',
            'w-1/12' => '1/12',
            'w-5/12' => '5/12',
            'w-7/12' => '7/12',
            'w-11/12' => '11/12',

            // Viewport units
            'w-svw' => 'Small Viewport',
            'w-lvw' => 'Large Viewport',
            'w-dvw' => 'Dynamic Viewport',

            // Container sizes
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

            // Rem-based values
            'w-0' => '0px',
            'w-px' => '1px',
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
        ];
    }

    public function getPageBuilderHeightList(): array
    {
        return [
            // Common utilities
            'h-auto' => 'Auto',
            'h-full' => 'Full',
            'h-screen' => 'Screen',
            'h-fit' => 'Fit Content',
            'h-min' => 'Min Content',
            'h-max' => 'Max Content',

            // Fractional heights
            'h-1/2' => '1/2',
            'h-1/3' => '1/3',
            'h-2/3' => '2/3',
            'h-1/4' => '1/4',
            'h-3/4' => '3/4',
            'h-1/5' => '1/5',
            'h-2/5' => '2/5',
            'h-3/5' => '3/5',
            'h-4/5' => '4/5',
            'h-1/6' => '1/6',
            'h-5/6' => '5/6',

            // Viewport units
            'h-svh' => 'Small Viewport',
            'h-lvh' => 'Large Viewport',
            'h-dvh' => 'Dynamic Viewport',

            // Rem-based values
            'h-0' => '0px',
            'h-px' => '1px',
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

    public function getBackdropBlurList(): array
    {
        return [
            '' => 'None',
            'backdrop-blur-none' => 'None (Explicit)',
            'backdrop-blur-sm' => 'Small',
            'backdrop-blur' => 'Default',
            'backdrop-blur-md' => 'Medium',
            'backdrop-blur-lg' => 'Large',
            'backdrop-blur-xl' => 'Extra Large',
            'backdrop-blur-2xl' => '2X Large',
            'backdrop-blur-3xl' => '3X Large',
        ];
    }

    public function getBlurList(): array
    {
        return [
            '' => 'None',
            'blur-none' => 'None (Explicit)',
            'blur-sm' => 'Small',
            'blur' => 'Default',
            'blur-md' => 'Medium',
            'blur-lg' => 'Large',
            'blur-xl' => 'Extra Large',
            'blur-2xl' => '2X Large',
            'blur-3xl' => '3X Large',
        ];
    }

    public function getDropShadowList(): array
    {
        return [
            '' => 'None',
            'drop-shadow-none' => 'None (Explicit)',
            'drop-shadow-sm' => 'Small',
            'drop-shadow' => 'Default',
            'drop-shadow-md' => 'Medium',
            'drop-shadow-lg' => 'Large',
            'drop-shadow-xl' => 'Extra Large',
            'drop-shadow-2xl' => '2X Large',
        ];
    }

    public function getFontSizeList(): array
    {
        return [
            '' => 'Default',
            'text-xs' => 'Extra Small',
            'text-sm' => 'Small',
            'text-base' => 'Base',
            'text-lg' => 'Large',
            'text-xl' => 'Extra Large',
            'text-2xl' => '2XL',
            'text-3xl' => '3XL',
            'text-4xl' => '4XL',
            'text-5xl' => '5XL',
            'text-6xl' => '6XL',
            'text-7xl' => '7XL',
            'text-8xl' => '8XL',
            'text-9xl' => '9XL',
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

        // Ensure color is a string before passing to parseColor (defensive against malformed client data)
        if (! is_string($color) && $color !== null) {
            $color = null;
        }

        return $this->parseColor($color, $defaultClass);
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="w-full animate-pulse">
            <div class="bg-base-300 rounded-sm h-48 w-full"></div>
        </div>
        HTML;
    }
}
