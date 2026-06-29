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
     * Localize a (possibly multilingual) value for display and, in view mode,
     * replace {variable} tokens with their registered values. In edit mode the
     * raw tokens are kept so they stay visible and editable in the builder.
     *
     * @param  mixed  $content
     * @return mixed
     */
    protected function localizeContent($content, ?string $locale = null)
    {
        $content = \pb_localize_content($content, $locale);

        if ($this->editMode || ! is_string($content)) {
            return $content;
        }

        return VariablesParser::parse($content);
    }

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
            (new FlexibleSizeProperty('mobileWidth', __('Mobile'), $widths, allowCustom: true, unit: 'px', defaultValue: $this->mobileWidth))
                ->setGroup('width', __('Width'), 3, 'heroicon-o-device-phone-mobile'),
            (new FlexibleSizeProperty('tabletWidth', __('Tablet'), $widths, allowCustom: true, unit: 'px', defaultValue: $this->tabletWidth))
                ->setGroup('width', __('Width'), 3, 'heroicon-o-device-tablet'),
            (new FlexibleSizeProperty('desktopWidth', __('Desktop'), $widths, allowCustom: true, unit: 'px', defaultValue: $this->desktopWidth))
                ->setGroup('width', __('Width'), 3, 'heroicon-o-computer-desktop'),

            (new FlexibleSizeProperty('mobileHeight', __('Mobile'), $heights, allowCustom: true, unit: 'px', defaultValue: $this->mobileHeight))
                ->setGroup('height', __('Height'), 3, 'heroicon-o-device-phone-mobile'),
            (new FlexibleSizeProperty('tabletHeight', __('Tablet'), $heights, allowCustom: true, unit: 'px', defaultValue: $this->tabletHeight))
                ->setGroup('height', __('Height'), 3, 'heroicon-o-device-tablet'),
            (new FlexibleSizeProperty('desktopHeight', __('Desktop'), $heights, allowCustom: true, unit: 'px', defaultValue: $this->desktopHeight))
                ->setGroup('height', __('Height'), 3, 'heroicon-o-computer-desktop'),

            (new FlexibleSizeProperty('mobileMinHeight', __('Mobile'), $minHeights, allowCustom: true, unit: 'px', defaultValue: $this->mobileMinHeight))
                ->setGroup('min_height', __('Min Height'), 3, 'heroicon-o-device-phone-mobile'),
            (new FlexibleSizeProperty('tabletMinHeight', __('Tablet'), $minHeights, allowCustom: true, unit: 'px', defaultValue: $this->tabletMinHeight))
                ->setGroup('min_height', __('Min Height'), 3, 'heroicon-o-device-tablet'),
            (new FlexibleSizeProperty('desktopMinHeight', __('Desktop'), $minHeights, allowCustom: true, unit: 'px', defaultValue: $this->desktopMinHeight))
                ->setGroup('min_height', __('Min Height'), 3, 'heroicon-o-computer-desktop'),
        ];
    }

    /**
     * Get visibility properties
     */
    protected function getVisibilityProperties(): array
    {
        return [
            (new CheckboxProperty('hiddenMobile', __('Mobile'), defaultValue: $this->hiddenMobile))
                ->setGroup('hide', __('Hide'), 3, 'heroicon-o-eye'),
            (new CheckboxProperty('hiddenTablet', __('Tablet'), defaultValue: $this->hiddenTablet))
                ->setGroup('hide', __('Hide'), 3, 'heroicon-o-eye'),
            (new CheckboxProperty('hiddenDesktop', __('Desktop'), defaultValue: $this->hiddenDesktop))
                ->setGroup('hide', __('Hide'), 3, 'heroicon-o-eye'),
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
            (new ResponsiveSpacingProperty('padding', __('Padding'), $paddingValues))
                ->setGroup('padding', __('Padding'), 1, 'heroicon-o-arrows-pointing-out'),
            (new ResponsiveSpacingProperty('margin', __('Margin'), $marginValues))
                ->setGroup('margin', __('Margin'), 1, 'heroicon-o-arrows-right-left'),
        ];
    }

    /**
     * Get style properties (colors)
     */
    protected function getStyleProperties(): array
    {
        $gradientDirections = [
            'to-t' => __('To Top'),
            'to-tr' => __('To Top Right'),
            'to-r' => __('To Right'),
            'to-br' => __('To Bottom Right'),
            'to-b' => __('To Bottom'),
            'to-bl' => __('To Bottom Left'),
            'to-l' => __('To Left'),
            'to-tl' => __('To Top Left'),
        ];

        return [
            (new ColorProperty('textColor', __('Text Color'), defaultValue: $this->textColor))
                ->setGroup('color', __('Color'), 2, 'heroicon-o-swatch'),
            (new ColorProperty('backgroundColor', __('Background Color'), defaultValue: $this->backgroundColor))
                ->setGroup('color', __('Color'), 2, 'heroicon-o-swatch'),

            // Background gradient
            (new ColorProperty('backgroundGradientFrom', __('From'), defaultValue: $this->backgroundGradientFrom))
                ->setGroup('bg_gradient', __('Background Gradient'), 3, 'heroicon-o-paint-brush'),
            (new ColorProperty('backgroundGradientTo', __('To'), defaultValue: $this->backgroundGradientTo))
                ->setGroup('bg_gradient', __('Background Gradient'), 3, 'heroicon-o-paint-brush'),
            (new SelectProperty('backgroundGradientDirection', __('Direction'), $gradientDirections, defaultValue: $this->backgroundGradientDirection))
                ->setGroup('bg_gradient', __('Background Gradient'), 3, 'heroicon-o-paint-brush'),

            // Text gradient
            (new ColorProperty('textGradientFrom', __('From'), defaultValue: $this->textGradientFrom))
                ->setGroup('text_gradient', __('Text Gradient'), 3, 'heroicon-o-paint-brush'),
            (new ColorProperty('textGradientTo', __('To'), defaultValue: $this->textGradientTo))
                ->setGroup('text_gradient', __('Text Gradient'), 3, 'heroicon-o-paint-brush'),
            (new SelectProperty('textGradientDirection', __('Direction'), $gradientDirections, defaultValue: $this->textGradientDirection))
                ->setGroup('text_gradient', __('Text Gradient'), 3, 'heroicon-o-paint-brush'),
        ];
    }

    /**
     * Get theme properties
     */
    protected function getThemeProperties(): array
    {
        return [
            new CheckboxProperty(name: 'forceDarkMode', label: __('Force Dark Mode'), defaultValue: $this->forceDarkMode),
        ];
    }

    /**
     * Get background image properties
     */
    protected function getBackgroundImageProperties(): array
    {
        return [
            (new ImageProperty('backgroundImage', __('Image'), defaultValue: $this->backgroundImage))
                ->setGroup('background_image', __('Background Image'), 1, 'heroicon-o-photo'),
            (new SelectProperty('backgroundPosition', __('Position'), [
                'center' => __('Center'),
                'top' => __('Top'),
                'right' => __('Right'),
                'bottom' => __('Bottom'),
                'left' => __('Left'),
                'top-left' => __('Top Left'),
                'top-right' => __('Top Right'),
                'bottom-left' => __('Bottom Left'),
                'bottom-right' => __('Bottom Right'),
            ], defaultValue: $this->backgroundPosition))
                ->setGroup('backgroundImageOptions', __('Background Image Options'), 2, 'heroicon-o-photo'),
            (new SelectProperty('backgroundSize', __('Size'), [
                'cover' => __('Cover'),
                'contain' => __('Contain'),
                'auto' => __('Auto'),
                '100%' => '100%',
            ], defaultValue: $this->backgroundSize))
                ->setGroup('backgroundImageOptions', __('Background Image Options'), 2, 'heroicon-o-photo'),
            (new SelectProperty('backgroundRepeat', __('Repeat'), [
                'no-repeat' => __('No Repeat'),
                'repeat' => __('Repeat'),
                'repeat-x' => __('Repeat X'),
                'repeat-y' => __('Repeat Y'),
            ], defaultValue: $this->backgroundRepeat))
                ->setGroup('backgroundImageOptions', __('Background Image Options'), 2, 'heroicon-o-photo'),
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
            (new SelectProperty('borderTopWidth', __('Top'), $borderWidths, defaultValue: $this->borderTopWidth))
                ->setGroup('border_width', __('Border Width'), 4, 'heroicon-o-square-3-stack-3d'),
            (new SelectProperty('borderRightWidth', __('Right'), $borderWidths, defaultValue: $this->borderRightWidth))
                ->setGroup('border_width', __('Border Width'), 4, 'heroicon-o-square-3-stack-3d'),
            (new SelectProperty('borderBottomWidth', __('Bottom'), $borderWidths, defaultValue: $this->borderBottomWidth))
                ->setGroup('border_width', __('Border Width'), 4, 'heroicon-o-square-3-stack-3d'),
            (new SelectProperty('borderLeftWidth', __('Left'), $borderWidths, defaultValue: $this->borderLeftWidth))
                ->setGroup('border_width', __('Border Width'), 4, 'heroicon-o-square-3-stack-3d'),
            (new SelectProperty('borderWidth', __('All'), $borderWidths, defaultValue: $this->borderWidth))
                ->setGroup('border_width', __('Border Width'), 4, 'heroicon-o-square-3-stack-3d'),

            // Border Color - All sides or individual
            (new ColorProperty('borderTopColor', __('Top'), defaultValue: $this->borderTopColor))
                ->setGroup('border_color', __('Border Color'), 4, 'heroicon-o-swatch'),
            (new ColorProperty('borderRightColor', __('Right'), defaultValue: $this->borderRightColor))
                ->setGroup('border_color', __('Border Color'), 4, 'heroicon-o-swatch'),
            (new ColorProperty('borderBottomColor', __('Bottom'), defaultValue: $this->borderBottomColor))
                ->setGroup('border_color', __('Border Color'), 4, 'heroicon-o-swatch'),
            (new ColorProperty('borderLeftColor', __('Left'), defaultValue: $this->borderLeftColor))
                ->setGroup('border_color', __('Border Color'), 4, 'heroicon-o-swatch'),
            (new ColorProperty('borderColor', __('All'), defaultValue: $this->borderColor))
                ->setGroup('border_color', __('Border Color'), 4, 'heroicon-o-swatch'),

            // Border Radius - All corners or individual
            (new SelectProperty('borderTopLeftRadius', __('Top Left'), $borderRadiusOptions, defaultValue: $this->borderTopLeftRadius))
                ->setGroup('border_radius', __('Border Radius'), 4, 'heroicon-o-square-2-stack'),
            (new SelectProperty('borderTopRightRadius', __('Top Right'), $borderRadiusOptions, defaultValue: $this->borderTopRightRadius))
                ->setGroup('border_radius', __('Border Radius'), 4, 'heroicon-o-square-2-stack'),
            (new SelectProperty('borderBottomRightRadius', __('Bottom Right'), $borderRadiusOptions, defaultValue: $this->borderBottomRightRadius))
                ->setGroup('border_radius', __('Border Radius'), 4, 'heroicon-o-square-2-stack'),
            (new SelectProperty('borderBottomLeftRadius', __('Bottom Left'), $borderRadiusOptions, defaultValue: $this->borderBottomLeftRadius))
                ->setGroup('border_radius', __('Border Radius'), 4, 'heroicon-o-square-2-stack'),
            (new SelectProperty('borderRadius', __('All'), $borderRadiusOptions, defaultValue: $this->borderRadius))
                ->setGroup('border_radius', __('Border Radius'), 4, 'heroicon-o-square-2-stack'),

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
            (new SelectProperty('boxShadow', __('Preset'), $boxShadowOptions, defaultValue: $this->boxShadow))
                ->setGroup('shadow_basic', __('Shadow Basic'), 3, 'heroicon-o-sun'),
            (new ColorProperty('boxShadowColor', __('Color'), defaultValue: $this->boxShadowColor))
                ->setGroup('shadow_basic', __('Shadow Basic'), 3, 'heroicon-o-sun'),
            (new TextProperty('boxShadowBlur', __('Blur'), numeric: true, defaultValue: $this->boxShadowBlur, min: 0, max: 100))
                ->setGroup('shadow_basic', __('Shadow Basic'), 3, 'heroicon-o-sun'),

            // Shadow Position (4 items)
            (new TextProperty('boxShadowOffsetX', __('Offset X'), numeric: true, defaultValue: $this->boxShadowOffsetX, min: -50, max: 50))
                ->setGroup('shadow_position', __('Shadow Position'), 4, 'heroicon-o-arrows-pointing-out'),
            (new TextProperty('boxShadowOffsetY', __('Offset Y'), numeric: true, defaultValue: $this->boxShadowOffsetY, min: -50, max: 50))
                ->setGroup('shadow_position', __('Shadow Position'), 4, 'heroicon-o-arrows-pointing-out'),
            (new TextProperty('boxShadowSpread', __('Spread'), numeric: true, defaultValue: $this->boxShadowSpread, min: -50, max: 50))
                ->setGroup('shadow_position', __('Shadow Position'), 4, 'heroicon-o-arrows-pointing-out'),
            (new CheckboxProperty('boxShadowInset', __('Inset'), defaultValue: $this->boxShadowInset))
                ->setGroup('shadow_position', __('Shadow Position'), 4, 'heroicon-o-arrows-pointing-out'),
        ];
    }

    /**
     * Get backdrop filter properties
     */
    protected function getBackdropFilterProperties(): array
    {
        $backdropBlurOptions = $this->getBackdropBlurList();

        return [
            (new SelectProperty('backdropBlur', __('Backdrop Blur'), $backdropBlurOptions, defaultValue: $this->backdropBlur))
                ->setGroup('backdrop_filters', __('Backdrop Filters'), 1, 'heroicon-o-sparkles'),
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
            (new SelectProperty('blur', __('Blur'), $blurOptions, defaultValue: $this->blur))
                ->setGroup('filters', __('Filters'), 2, 'heroicon-o-adjustments-horizontal'),
            (new SelectProperty('dropShadow', __('Drop Shadow'), $dropShadowOptions, defaultValue: $this->dropShadow))
                ->setGroup('filters', __('Filters'), 2, 'heroicon-o-adjustments-horizontal'),
        ];
    }

    /**
     * Get layout properties (container, alignment)
     */
    protected function getLayoutProperties(): array
    {
        return [
            (new CheckboxProperty('selfCentered', __('Self Centered'), defaultValue: $this->selfCentered))
                ->setGroup('layout', __('Layout'), 3, 'heroicon-o-rectangle-group'),
            (new SelectProperty('position', __('Position'), [
                '' => __('Static (Default)'),
                'relative' => __('Relative'),
                'absolute' => __('Absolute'),
                'fixed' => __('Fixed'),
                'sticky' => __('Sticky'),
            ], defaultValue: $this->position))
                ->setGroup('layout', __('Layout'), 3, 'heroicon-o-rectangle-group'),
            (new SelectProperty('zIndex', __('Z-Index'), [
                '' => __('Auto (Default)'),
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
                ->setGroup('layout', __('Layout'), 3, 'heroicon-o-rectangle-group'),
        ];
    }

    /**
     * Get transform properties (rotate, scale, translate, skew)
     */
    protected function getTransformProperties(): array
    {
        return [
            (new TextProperty('mobileRotate', __('Mobile'), numeric: true, defaultValue: $this->mobileRotate))
                ->setGroup('rotate', __('Rotate (deg)'), 3, 'heroicon-o-arrow-path'),
            (new TextProperty('tabletRotate', __('Tablet'), numeric: true, defaultValue: $this->tabletRotate))
                ->setGroup('rotate', __('Rotate (deg)'), 3, 'heroicon-o-arrow-path'),
            (new TextProperty('desktopRotate', __('Desktop'), numeric: true, defaultValue: $this->desktopRotate))
                ->setGroup('rotate', __('Rotate (deg)'), 3, 'heroicon-o-arrow-path'),

            (new TextProperty('mobileScale', __('Mobile'), numeric: true, defaultValue: $this->mobileScale))
                ->setGroup('scale', __('Scale'), 3, 'heroicon-o-arrows-pointing-out'),
            (new TextProperty('tabletScale', __('Tablet'), numeric: true, defaultValue: $this->tabletScale))
                ->setGroup('scale', __('Scale'), 3, 'heroicon-o-arrows-pointing-out'),
            (new TextProperty('desktopScale', __('Desktop'), numeric: true, defaultValue: $this->desktopScale))
                ->setGroup('scale', __('Scale'), 3, 'heroicon-o-arrows-pointing-out'),

            (new TextProperty('mobileTranslateX', __('Mobile'), numeric: true, defaultValue: $this->mobileTranslateX))
                ->setGroup('translateX', __('Translate X (px)'), 3, 'heroicon-o-arrows-right-left'),
            (new TextProperty('tabletTranslateX', __('Tablet'), numeric: true, defaultValue: $this->tabletTranslateX))
                ->setGroup('translateX', __('Translate X (px)'), 3, 'heroicon-o-arrows-right-left'),
            (new TextProperty('desktopTranslateX', __('Desktop'), numeric: true, defaultValue: $this->desktopTranslateX))
                ->setGroup('translateX', __('Translate X (px)'), 3, 'heroicon-o-arrows-right-left'),

            (new TextProperty('mobileTranslateY', __('Mobile'), numeric: true, defaultValue: $this->mobileTranslateY))
                ->setGroup('translateY', __('Translate Y (px)'), 3, 'heroicon-o-arrows-up-down'),
            (new TextProperty('tabletTranslateY', __('Tablet'), numeric: true, defaultValue: $this->tabletTranslateY))
                ->setGroup('translateY', __('Translate Y (px)'), 3, 'heroicon-o-arrows-up-down'),
            (new TextProperty('desktopTranslateY', __('Desktop'), numeric: true, defaultValue: $this->desktopTranslateY))
                ->setGroup('translateY', __('Translate Y (px)'), 3, 'heroicon-o-arrows-up-down'),

            (new TextProperty('mobileSkewX', __('Mobile'), numeric: true, defaultValue: $this->mobileSkewX))
                ->setGroup('skewX', __('Skew X (deg)'), 3, 'heroicon-o-forward'),
            (new TextProperty('tabletSkewX', __('Tablet'), numeric: true, defaultValue: $this->tabletSkewX))
                ->setGroup('skewX', __('Skew X (deg)'), 3, 'heroicon-o-forward'),
            (new TextProperty('desktopSkewX', __('Desktop'), numeric: true, defaultValue: $this->desktopSkewX))
                ->setGroup('skewX', __('Skew X (deg)'), 3, 'heroicon-o-forward'),

            (new TextProperty('mobileSkewY', __('Mobile'), numeric: true, defaultValue: $this->mobileSkewY))
                ->setGroup('skewY', __('Skew Y (deg)'), 3, 'heroicon-o-backward'),
            (new TextProperty('tabletSkewY', __('Tablet'), numeric: true, defaultValue: $this->tabletSkewY))
                ->setGroup('skewY', __('Skew Y (deg)'), 3, 'heroicon-o-backward'),
            (new TextProperty('desktopSkewY', __('Desktop'), numeric: true, defaultValue: $this->desktopSkewY))
                ->setGroup('skewY', __('Skew Y (deg)'), 3, 'heroicon-o-backward'),
        ];
    }

    /**
     * Get text properties (alignment)
     */
    protected function getTextProperties(): array
    {
        return [
            (new SelectProperty('textAlign', __('Text Align'), [
                '' => __('Default'),
                'text-left' => __('Left'),
                'text-center' => __('Center'),
                'text-right' => __('Right'),
                'text-justify' => __('Justify'),
                'text-start' => __('Start'),
                'text-end' => __('End'),
            ], defaultValue: $this->textAlign))
                ->setGroup('text', __('Text'), 1, 'heroicon-o-language'),
        ];
    }

    /**
     * Get typography properties (font size)
     */
    protected function getTypographyProperties(): array
    {
        $fontSizes = $this->getFontSizeList();

        return [
            (new SelectProperty(name: 'mobileFontSize', label: __('Mobile'), options: $fontSizes, defaultValue: $this->mobileFontSize))
                ->setGroup(group: 'font', groupLabel: __('Text Size'), columns: 3, groupIcon: 'heroicon-o-bars-3-bottom-left'),
            (new SelectProperty(name: 'tabletFontSize', label: __('Tablet'), options: $fontSizes, defaultValue: $this->tabletFontSize))
                ->setGroup(group: 'font', groupLabel: __('Text Size'), columns: 3, groupIcon: 'heroicon-o-bars-3-bottom-left'),
            (new SelectProperty(name: 'desktopFontSize', label: __('Desktop'), options: $fontSizes, defaultValue: $this->desktopFontSize))
                ->setGroup(group: 'font', groupLabel: __('Text Size'), columns: 3, groupIcon: 'heroicon-o-bars-3-bottom-left'),
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
     * Get the flat list of shared (layout/style) property keys.
     */
    public function getSharedPropertyKeys(): array
    {
        $keys = [];
        foreach ($this->getSharedProperties() as $property) {
            if ($property instanceof ResponsiveSpacingProperty) {
                $keys = array_merge($keys, array_keys($property->getFieldDefaults()));
            } else {
                $keys[] = $property->name;
            }
        }

        return $keys;
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
            'w-auto' => __('Auto'),
            'w-full' => __('Full'),
            'w-screen' => __('Screen'),
            'w-fit' => __('Fit Content'),
            'w-min' => __('Min Content'),
            'w-max' => __('Max Content'),

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
            'w-svw' => __('Small Viewport'),
            'w-lvw' => __('Large Viewport'),
            'w-dvw' => __('Dynamic Viewport'),

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
            'h-auto' => __('Auto'),
            'h-full' => __('Full'),
            'h-screen' => __('Screen'),
            'h-fit' => __('Fit Content'),
            'h-min' => __('Min Content'),
            'h-max' => __('Max Content'),

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
            'h-svh' => __('Small Viewport'),
            'h-lvh' => __('Large Viewport'),
            'h-dvh' => __('Dynamic Viewport'),

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
            'min-h-fit' => __('Fit Content'),
            'min-h-min' => __('Min Content'),
            'min-h-max' => __('Max Content'),
            'min-h-full' => __('Full'),
            'min-h-screen' => __('Screen'),
            'min-h-svh' => __('Small Viewport'),
            'min-h-lvh' => __('Large Viewport'),
            'min-h-dvh' => __('Dynamic Viewport'),
        ];
    }

    public function getBorderWidthList(): array
    {
        return [
            '' => __('None'),
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
            '' => __('None'),
            'rounded-none' => __('None'),
            'rounded-sm' => __('Small'),
            'rounded' => __('Default'),
            'rounded-md' => __('Medium'),
            'rounded-lg' => __('Large'),
            'rounded-xl' => __('Extra Large'),
            'rounded-2xl' => __('2X Large'),
            'rounded-3xl' => __('3X Large'),
            'rounded-full' => __('Full'),
        ];
    }

    public function getBoxShadowList(): array
    {
        return [
            '' => __('None'),
            'shadow-sm' => __('Small'),
            'shadow' => __('Default'),
            'shadow-md' => __('Medium'),
            'shadow-lg' => __('Large'),
            'shadow-xl' => __('Extra Large'),
            'shadow-2xl' => __('2X Large'),
            'shadow-inner' => __('Inner'),
            'shadow-none' => __('None'),
        ];
    }

    public function getBackdropBlurList(): array
    {
        return [
            '' => __('None'),
            'backdrop-blur-none' => __('None (Explicit)'),
            'backdrop-blur-sm' => __('Small'),
            'backdrop-blur' => __('Default'),
            'backdrop-blur-md' => __('Medium'),
            'backdrop-blur-lg' => __('Large'),
            'backdrop-blur-xl' => __('Extra Large'),
            'backdrop-blur-2xl' => __('2X Large'),
            'backdrop-blur-3xl' => __('3X Large'),
        ];
    }

    public function getBlurList(): array
    {
        return [
            '' => __('None'),
            'blur-none' => __('None (Explicit)'),
            'blur-sm' => __('Small'),
            'blur' => __('Default'),
            'blur-md' => __('Medium'),
            'blur-lg' => __('Large'),
            'blur-xl' => __('Extra Large'),
            'blur-2xl' => __('2X Large'),
            'blur-3xl' => __('3X Large'),
        ];
    }

    public function getDropShadowList(): array
    {
        return [
            '' => __('None'),
            'drop-shadow-none' => __('None (Explicit)'),
            'drop-shadow-sm' => __('Small'),
            'drop-shadow' => __('Default'),
            'drop-shadow-md' => __('Medium'),
            'drop-shadow-lg' => __('Large'),
            'drop-shadow-xl' => __('Extra Large'),
            'drop-shadow-2xl' => __('2X Large'),
        ];
    }

    public function getFontSizeList(): array
    {
        return [
            '' => __('Default'),
            'text-xs' => __('Extra Small'),
            'text-sm' => __('Small'),
            'text-base' => __('Base'),
            'text-lg' => __('Large'),
            'text-xl' => __('Extra Large'),
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
