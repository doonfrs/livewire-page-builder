<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Illuminate\Support\Str;
use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Blocks\RichText;
use Trinavo\LivewirePageBuilder\Blocks\Spacer;
use Trinavo\LivewirePageBuilder\Http\Livewire\BuilderPageBlock;
use Trinavo\LivewirePageBuilder\Http\Livewire\RowBlock;

class PageBuilderService
{
    public function getAvailableBlocks(): array
    {
        $blocks = [];

        foreach ($this->getConfigBlocks() as $blockClass) {
            $instance = app($blockClass);
            $alias = $this->getClassAlias($blockClass);
            $blocks[] = [
                'class' => $blockClass,
                'alias' => $alias,
                'label' => $instance->getPageBuilderLabel(),
                'icon' => $instance->getPageBuilderIcon(),
            ];
        }

        foreach ($this->getConfigBlocksPages() as $blockName => $blockInfo) {
            if (is_int($blockName)) {
                continue;
            }
            if (isset($blockInfo['is_block']) && $blockInfo['is_block']) {
                $instance = app(BuilderPageBlock::class);
                $instance->blockPageName = $blockName;
                $alias = 'builder-page-block';
                $blocks[] = [
                    'class' => BuilderPageBlock::class,
                    'alias' => $alias,
                    'label' => $instance->getPageBuilderLabel(),
                    'icon' => $instance->getPageBuilderIcon(),
                    'blockPageName' => $blockName,
                ];
            }
        }

        return $blocks;
    }

    public function getConfigBlocks(): array
    {
        $blocks = config('page-builder.blocks', []);
        $blocks[] = Spacer::class;
        $blocks[] = RichText::class;
        $blocks[] = RowBlock::class;

        return $blocks;
    }

    public function getConfigBlocksPages(): array
    {
        return config('page-builder.pages', []);
    }

    public function registerBlocks(): void
    {
        foreach ($this->getConfigBlocks() as $blockClass) {
            $alias = $this->getClassAlias($blockClass);
            Livewire::component($alias, $blockClass);
        }
    }

    public function getClassAlias($blockClass): string
    {
        $alias = Str::kebab(str_replace('\\', '-', $blockClass));
        $alias = str_replace('--', '-', $alias);
        $alias = 'page-builder-'.$alias;

        return $alias;
    }

    public function getClassNameFromAlias($alias): ?string
    {
        if ($alias === 'builder-page-block') {
            return BuilderPageBlock::class;
        }
        foreach ($this->getConfigBlocks() as $blockClass) {
            if ($this->getClassAlias($blockClass) === $alias) {
                return $blockClass;
            }
        }

        return null;
    }

    public function getRowCssClassesFromProperties($properties): string
    {
        $classes = [];
        $flex = $properties['flex'] ?? null;
        if ($flex) {
            $classes[] = "flex flex-{$flex}";
        }

        // Add vertical alignment to the flex container
        $contentAlign = $properties['contentAlign'] ?? 'content-center';
        if ($flex) {  // Only add vertical alignment when flex is active
            // Map content-* classes to appropriate flexbox alignment classes
            $alignmentMap = [
                'content-start' => 'justify-start',
                'content-center' => 'justify-center',
                'content-end' => 'justify-end',
                'content-stretch' => 'justify-stretch',
            ];

            $justifyClass = $alignmentMap[$contentAlign] ?? 'justify-center';
            $classes[] = $justifyClass;
        }

        // Add position property
        $position = $properties['position'] ?? null;
        if ($position) {
            $classes[] = $position;
        }

        // Add overflow-x property
        $overflowX = $properties['overflowX'] ?? '';
        if ($overflowX && $overflowX !== '') {
            $classes[] = "overflow-x-{$overflowX}";
        }

        // Add overflow-y property
        $overflowY = $properties['overflowY'] ?? '';
        if ($overflowY && $overflowY !== '') {
            $classes[] = "overflow-y-{$overflowY}";
        }

        $contentWidthMobile = $properties['contentWidthMobile'] ?? 'w-full';
        $contentWidthTablet = $properties['contentWidthTablet'] ?? 'w-full';
        $contentWidthDesktop = $properties['contentWidthDesktop'] ?? 'w-full';

        $mobileGap = $properties['mobileGap'] ?? null;
        $tabletGap = $properties['tabletGap'] ?? null;
        $desktopGap = $properties['desktopGap'] ?? null;

        if ($contentWidthMobile) {
            $classes[] = $contentWidthMobile;
        }

        // Only add tablet/desktop widths if they're different from mobile
        if ($contentWidthTablet !== $contentWidthMobile) {
            $classes[] = '@3xl:'.$contentWidthTablet;
        }

        if ($contentWidthDesktop !== $contentWidthTablet) {
            $classes[] = '@5xl:'.$contentWidthDesktop;
        }

        if ($mobileGap) {
            $classes[] = "gap-$mobileGap";
        }

        if ($tabletGap) {
            $classes[] = "@3xl:gap-$tabletGap";
        }

        if ($desktopGap) {
            $classes[] = "@5xl:gap-$desktopGap";
        }

        $classes[] = 'h-full';

        return implode(' ', $classes);
    }

    public function getCssClassesFromProperties(array $properties): ?string
    {
        $hiddenMobile = $properties['hiddenMobile'] ?? false;
        $hiddenTablet = $properties['hiddenTablet'] ?? false;
        $hiddenDesktop = $properties['hiddenDesktop'] ?? false;

        // Device-specific padding
        $mobilePaddingTop = $properties['mobilePaddingTop'] ?? 0;
        $mobilePaddingRight = $properties['mobilePaddingRight'] ?? 0;
        $mobilePaddingBottom = $properties['mobilePaddingBottom'] ?? 0;
        $mobilePaddingLeft = $properties['mobilePaddingLeft'] ?? 0;

        $tabletPaddingTop = $properties['tabletPaddingTop'] ?? 0;
        $tabletPaddingRight = $properties['tabletPaddingRight'] ?? 0;
        $tabletPaddingBottom = $properties['tabletPaddingBottom'] ?? 0;
        $tabletPaddingLeft = $properties['tabletPaddingLeft'] ?? 0;

        $desktopPaddingTop = $properties['desktopPaddingTop'] ?? 0;
        $desktopPaddingRight = $properties['desktopPaddingRight'] ?? 0;
        $desktopPaddingBottom = $properties['desktopPaddingBottom'] ?? 0;
        $desktopPaddingLeft = $properties['desktopPaddingLeft'] ?? 0;

        // Device-specific margin
        $mobileMarginTop = $properties['mobileMarginTop'] ?? 0;
        $mobileMarginRight = $properties['mobileMarginRight'] ?? 0;
        $mobileMarginBottom = $properties['mobileMarginBottom'] ?? 0;
        $mobileMarginLeft = $properties['mobileMarginLeft'] ?? 0;

        $tabletMarginTop = $properties['tabletMarginTop'] ?? 0;
        $tabletMarginRight = $properties['tabletMarginRight'] ?? 0;
        $tabletMarginBottom = $properties['tabletMarginBottom'] ?? 0;
        $tabletMarginLeft = $properties['tabletMarginLeft'] ?? 0;

        $desktopMarginTop = $properties['desktopMarginTop'] ?? 0;
        $desktopMarginRight = $properties['desktopMarginRight'] ?? 0;
        $desktopMarginBottom = $properties['desktopMarginBottom'] ?? 0;
        $desktopMarginLeft = $properties['desktopMarginLeft'] ?? 0;

        $selfCentered = $properties['selfCentered'] ?? false;
        $textColor = $properties['textColor'] ?? null;
        $backgroundColor = $properties['backgroundColor'] ?? null;

        $classes = [];

        if ($hiddenMobile && $hiddenTablet && $hiddenDesktop) {
            $classes[] = 'hidden';
        } elseif ($hiddenMobile && $hiddenTablet) {
            $classes[] = 'hidden @5xl:block';
        } elseif ($hiddenMobile && $hiddenDesktop) {
            $classes[] = 'hidden @xl:block @5xl:hidden';
        } elseif ($hiddenTablet && $hiddenDesktop) {
            $classes[] = 'block @xl:hidden';
        } elseif ($hiddenMobile) {
            $classes[] = 'hidden @xl:block';
        } elseif ($hiddenTablet) {
            $classes[] = 'block @xl:hidden @5xl:block';
        } elseif ($hiddenDesktop) {
            $classes[] = 'block @5xl:hidden';
        } else {
            if ($selfCentered) {
                $classes[] = 'block';
            } else {
                $classes[] = 'inline-block';
            }
        }

        if ($selfCentered) {
            $classes[] = 'mx-auto';
        }

        // Add responsive padding classes
        // Mobile padding
        if ($mobilePaddingTop > 0) {
            $classes[] = "pt-$mobilePaddingTop";
        }
        if ($mobilePaddingRight > 0) {
            $classes[] = "pr-$mobilePaddingRight";
        }
        if ($mobilePaddingBottom > 0) {
            $classes[] = "pb-$mobilePaddingBottom";
        }
        if ($mobilePaddingLeft > 0) {
            $classes[] = "pl-$mobilePaddingLeft";
        }

        // Tablet padding (only if different from mobile)
        if ($tabletPaddingTop != $mobilePaddingTop && $tabletPaddingTop > 0) {
            $classes[] = "@3xl:pt-$tabletPaddingTop";
        }
        if ($tabletPaddingRight != $mobilePaddingRight && $tabletPaddingRight > 0) {
            $classes[] = "@3xl:pr-$tabletPaddingRight";
        }
        if ($tabletPaddingBottom != $mobilePaddingBottom && $tabletPaddingBottom > 0) {
            $classes[] = "@3xl:pb-$tabletPaddingBottom";
        }
        if ($tabletPaddingLeft != $mobilePaddingLeft && $tabletPaddingLeft > 0) {
            $classes[] = "@3xl:pl-$tabletPaddingLeft";
        }

        // Desktop padding (only if different from tablet)
        if ($desktopPaddingTop != $tabletPaddingTop && $desktopPaddingTop > 0) {
            $classes[] = "@5xl:pt-$desktopPaddingTop";
        }
        if ($desktopPaddingRight != $tabletPaddingRight && $desktopPaddingRight > 0) {
            $classes[] = "@5xl:pr-$desktopPaddingRight";
        }
        if ($desktopPaddingBottom != $tabletPaddingBottom && $desktopPaddingBottom > 0) {
            $classes[] = "@5xl:pb-$desktopPaddingBottom";
        }
        if ($desktopPaddingLeft != $tabletPaddingLeft && $desktopPaddingLeft > 0) {
            $classes[] = "@5xl:pl-$desktopPaddingLeft";
        }

        // Add responsive margin classes
        // Mobile margin
        if ($mobileMarginTop != 0) {
            $classes[] = $mobileMarginTop < 0 ? "-mt-" . abs($mobileMarginTop) : "mt-$mobileMarginTop";
        }
        if ($mobileMarginRight != 0) {
            $classes[] = $mobileMarginRight < 0 ? "-mr-" . abs($mobileMarginRight) : "mr-$mobileMarginRight";
        }
        if ($mobileMarginBottom != 0) {
            $classes[] = $mobileMarginBottom < 0 ? "-mb-" . abs($mobileMarginBottom) : "mb-$mobileMarginBottom";
        }
        if ($mobileMarginLeft != 0) {
            $classes[] = $mobileMarginLeft < 0 ? "-ml-" . abs($mobileMarginLeft) : "ml-$mobileMarginLeft";
        }

        // Tablet margin (only if different from mobile)
        if ($tabletMarginTop != $mobileMarginTop && $tabletMarginTop != 0) {
            $classes[] = $tabletMarginTop < 0 ? "@3xl:-mt-" . abs($tabletMarginTop) : "@3xl:mt-$tabletMarginTop";
        }
        if ($tabletMarginRight != $mobileMarginRight && $tabletMarginRight != 0) {
            $classes[] = $tabletMarginRight < 0 ? "@3xl:-mr-" . abs($tabletMarginRight) : "@3xl:mr-$tabletMarginRight";
        }
        if ($tabletMarginBottom != $mobileMarginBottom && $tabletMarginBottom != 0) {
            $classes[] = $tabletMarginBottom < 0 ? "@3xl:-mb-" . abs($tabletMarginBottom) : "@3xl:mb-$tabletMarginBottom";
        }
        if ($tabletMarginLeft != $mobileMarginLeft && $tabletMarginLeft != 0) {
            $classes[] = $tabletMarginLeft < 0 ? "@3xl:-ml-" . abs($tabletMarginLeft) : "@3xl:ml-$tabletMarginLeft";
        }

        // Desktop margin (only if different from tablet)
        if ($desktopMarginTop != $tabletMarginTop && $desktopMarginTop != 0) {
            $classes[] = $desktopMarginTop < 0 ? "@5xl:-mt-" . abs($desktopMarginTop) : "@5xl:mt-$desktopMarginTop";
        }
        if ($desktopMarginRight != $tabletMarginRight && $desktopMarginRight != 0) {
            $classes[] = $desktopMarginRight < 0 ? "@5xl:-mr-" . abs($desktopMarginRight) : "@5xl:mr-$desktopMarginRight";
        }
        if ($desktopMarginBottom != $tabletMarginBottom && $desktopMarginBottom != 0) {
            $classes[] = $desktopMarginBottom < 0 ? "@5xl:-mb-" . abs($desktopMarginBottom) : "@5xl:mb-$desktopMarginBottom";
        }
        if ($desktopMarginLeft != $tabletMarginLeft && $desktopMarginLeft != 0) {
            $classes[] = $desktopMarginLeft < 0 ? "@5xl:-ml-" . abs($desktopMarginLeft) : "@5xl:ml-$desktopMarginLeft";
        }

        if ($textColor) {
            if (! str_starts_with($textColor, '#')) {
                $classes[] = "text-$textColor";
            }
        }

        // Add background color classes or inline styles for hex colors
        if ($backgroundColor) {
            if (! str_starts_with($backgroundColor, '#')) {
                $classes[] = "bg-$backgroundColor";
            }
        }

        // Add border classes
        $borderClasses = $this->getBorderCssClassesFromProperties($properties);
        if (count($borderClasses) > 0) {
            $classes = array_merge($classes, $borderClasses);
        }

        // Add box shadow classes
        $boxShadowClasses = $this->getBoxShadowCssClassesFromProperties($properties);
        if (count($boxShadowClasses) > 0) {
            $classes = array_merge($classes, $boxShadowClasses);
        }

        // Vertical alignment - allow customization instead of hardcoded centering
        $contentAlign = $properties['contentAlign'] ?? 'content-center';
        $classes[] = $contentAlign;

        // Add position property
        $position = $properties['position'] ?? null;
        if ($position) {
            $classes[] = $position;
        }

        $classString = implode(' ', array_unique($classes));

        $heightClasses = $this->getHeightCssClassesFromProperties($properties);
        if (trim($heightClasses) !== '') {
            $classString .= ' '.$heightClasses;
        }

        $widthClasses = $this->getWidthCssClassesFromProperties($properties);
        if (count($widthClasses) > 0) {
            $classString .= ' '.implode(' ', $widthClasses);
        }

        return $classString;
    }

    public function getWidthCssClassesFromProperties(array $properties): array
    {
        $mobileWidth = $properties['mobileWidth'] ?? 'w-auto';
        $tabletWidth = $properties['tabletWidth'] ?? 'w-auto';
        $desktopWidth = $properties['desktopWidth'] ?? 'w-auto';

        $classes = [];

        // Format width values (in case they are custom arbitrary values or classes)
        $classes[] = $this->formatSizeValue($mobileWidth, 'w');

        // Only add tablet/desktop widths if they're different from mobile
        if ($tabletWidth !== $mobileWidth) {
            $classes[] = '@3xl:'.$this->formatSizeValue($tabletWidth, 'w');
        }

        if ($desktopWidth !== $tabletWidth) {
            $classes[] = '@5xl:'.$this->formatSizeValue($desktopWidth, 'w');
        }

        return $classes;
    }

    public function getInlineStylesFromProperties(array $properties): ?string
    {
        $textColor = $properties['textColor'] ?? null;
        $backgroundColor = $properties['backgroundColor'] ?? null;
        $backgroundImage = $properties['backgroundImage'] ?? null;
        $backgroundPosition = $properties['backgroundPosition'] ?? 'center';
        $backgroundSize = $properties['backgroundSize'] ?? 'cover';
        $backgroundRepeat = $properties['backgroundRepeat'] ?? 'no-repeat';

        $styles = [];
        // Add text color classes or inline styles for hex colors
        if ($textColor) {
            if (str_starts_with($textColor, '#')) {
                $styles[] = "color: $textColor";
            }
        }

        // Add background color classes or inline styles for hex colors
        if ($backgroundColor) {
            if (str_starts_with($backgroundColor, '#')) {
                $styles[] = "background-color: $backgroundColor";
            }
        }

        // Add background image styles
        if ($backgroundImage) {
            $styles[] = "background-image: url('$backgroundImage')";
            $styles[] = "background-position: $backgroundPosition";
            $styles[] = "background-size: $backgroundSize";
            $styles[] = "background-repeat: $backgroundRepeat";
        }

        // Add border color styles for hex colors
        $borderStyles = $this->getBorderInlineStylesFromProperties($properties);
        if (count($borderStyles) > 0) {
            $styles = array_merge($styles, $borderStyles);
        }

        // Add box shadow styles for hex colors
        $boxShadowStyles = $this->getBoxShadowInlineStylesFromProperties($properties);
        if (count($boxShadowStyles) > 0) {
            $styles = array_merge($styles, $boxShadowStyles);
        }

        $styleString = implode(';', $styles);
        if (trim($styleString) !== '') {
            $styleString .= ';';
        }

        return $styleString;
    }

    public function getHeightCssClassesFromProperties(array $properties): string
    {
        $mobileHeight = $properties['mobileHeight'] ?? null;
        $tabletHeight = $properties['tabletHeight'] ?? null;
        $desktopHeight = $properties['desktopHeight'] ?? null;
        $mobileMinHeight = $properties['mobileMinHeight'] ?? null;
        $tabletMinHeight = $properties['tabletMinHeight'] ?? null;
        $desktopMinHeight = $properties['desktopMinHeight'] ?? null;

        $classes = [];

        // Handle height values
        if ($mobileHeight) {
            $classes[] = $this->formatSizeValue($mobileHeight, 'h');
        }

        if ($tabletHeight && $tabletHeight !== $mobileHeight) {
            $classes[] = '@3xl:'.$this->formatSizeValue($tabletHeight, 'h');
        }

        if ($desktopHeight && $desktopHeight !== $tabletHeight) {
            $classes[] = '@5xl:'.$this->formatSizeValue($desktopHeight, 'h');
        }

        // Handle min-height values
        if ($mobileMinHeight) {
            $classes[] = $this->formatSizeValue($mobileMinHeight, 'min-h');
        }

        if ($tabletMinHeight && $tabletMinHeight !== $mobileMinHeight) {
            $classes[] = '@3xl:'.$this->formatSizeValue($tabletMinHeight, 'min-h');
        }

        if ($desktopMinHeight && $desktopMinHeight !== $tabletMinHeight) {
            $classes[] = '@5xl:'.$this->formatSizeValue($desktopMinHeight, 'min-h');
        }

        return implode(' ', $classes);
    }

    /**
     * Get border CSS classes from properties
     */
    public function getBorderCssClassesFromProperties(array $properties): array
    {
        $classes = [];

        // Border width classes
        $borderWidth = $properties['borderWidth'] ?? null;
        $borderTopWidth = $properties['borderTopWidth'] ?? null;
        $borderRightWidth = $properties['borderRightWidth'] ?? null;
        $borderBottomWidth = $properties['borderBottomWidth'] ?? null;
        $borderLeftWidth = $properties['borderLeftWidth'] ?? null;

        // Border color properties
        $borderColor = $properties['borderColor'] ?? null;
        $borderTopColor = $properties['borderTopColor'] ?? null;
        $borderRightColor = $properties['borderRightColor'] ?? null;
        $borderBottomColor = $properties['borderBottomColor'] ?? null;
        $borderLeftColor = $properties['borderLeftColor'] ?? null;

        // Border radius properties
        $borderRadius = $properties['borderRadius'] ?? null;
        $borderTopLeftRadius = $properties['borderTopLeftRadius'] ?? null;
        $borderTopRightRadius = $properties['borderTopRightRadius'] ?? null;
        $borderBottomRightRadius = $properties['borderBottomRightRadius'] ?? null;
        $borderBottomLeftRadius = $properties['borderBottomLeftRadius'] ?? null;

        // Add border width classes
        if ($borderWidth) {
            $classes[] = $borderWidth;
        } else {
            // Individual border widths
            if ($borderTopWidth) {
                $classes[] = $this->convertBorderDirection($borderTopWidth, 't');
            }
            if ($borderRightWidth) {
                $classes[] = $this->convertBorderDirection($borderRightWidth, 'r');
            }
            if ($borderBottomWidth) {
                $classes[] = $this->convertBorderDirection($borderBottomWidth, 'b');
            }
            if ($borderLeftWidth) {
                $classes[] = $this->convertBorderDirection($borderLeftWidth, 'l');
            }
        }

        // Add border color classes (only for non-hex colors)
        if ($borderColor && ! str_starts_with($borderColor, '#')) {
            $classes[] = "border-$borderColor";
        } else {
            // Individual border colors (only for non-hex colors)
            if ($borderTopColor && ! str_starts_with($borderTopColor, '#')) {
                $classes[] = "border-t-$borderTopColor";
            }
            if ($borderRightColor && ! str_starts_with($borderRightColor, '#')) {
                $classes[] = "border-r-$borderRightColor";
            }
            if ($borderBottomColor && ! str_starts_with($borderBottomColor, '#')) {
                $classes[] = "border-b-$borderBottomColor";
            }
            if ($borderLeftColor && ! str_starts_with($borderLeftColor, '#')) {
                $classes[] = "border-l-$borderLeftColor";
            }
        }

        // Add border radius classes
        if ($borderRadius) {
            $classes[] = $borderRadius;
        } else {
            // Individual border radius
            if ($borderTopLeftRadius) {
                $classes[] = $this->convertBorderRadiusDirection($borderTopLeftRadius, 'tl');
            }
            if ($borderTopRightRadius) {
                $classes[] = $this->convertBorderRadiusDirection($borderTopRightRadius, 'tr');
            }
            if ($borderBottomRightRadius) {
                $classes[] = $this->convertBorderRadiusDirection($borderBottomRightRadius, 'br');
            }
            if ($borderBottomLeftRadius) {
                $classes[] = $this->convertBorderRadiusDirection($borderBottomLeftRadius, 'bl');
            }
        }

        return $classes;
    }

    /**
     * Get border inline styles for hex colors
     */
    public function getBorderInlineStylesFromProperties(array $properties): array
    {
        $styles = [];

        // Border color properties
        $borderColor = $properties['borderColor'] ?? null;
        $borderTopColor = $properties['borderTopColor'] ?? null;
        $borderRightColor = $properties['borderRightColor'] ?? null;
        $borderBottomColor = $properties['borderBottomColor'] ?? null;
        $borderLeftColor = $properties['borderLeftColor'] ?? null;

        // Add border color styles for hex colors
        if ($borderColor && str_starts_with($borderColor, '#')) {
            $styles[] = "border-color: $borderColor";
        } else {
            // Individual border colors for hex values
            if ($borderTopColor && str_starts_with($borderTopColor, '#')) {
                $styles[] = "border-top-color: $borderTopColor";
            }
            if ($borderRightColor && str_starts_with($borderRightColor, '#')) {
                $styles[] = "border-right-color: $borderRightColor";
            }
            if ($borderBottomColor && str_starts_with($borderBottomColor, '#')) {
                $styles[] = "border-bottom-color: $borderBottomColor";
            }
            if ($borderLeftColor && str_starts_with($borderLeftColor, '#')) {
                $styles[] = "border-left-color: $borderLeftColor";
            }
        }

        return $styles;
    }

    /**
     * Convert border width class to directional border class
     */
    protected function convertBorderDirection($borderClass, $direction): string
    {
        if (! $borderClass) {
            return '';
        }

        // Handle the different border width formats
        if ($borderClass === 'border') {
            return "border-{$direction}";
        }

        if (str_starts_with($borderClass, 'border-')) {
            // Extract the width part (e.g., '2', '4', '8', '0')
            $width = str_replace('border-', '', $borderClass);

            return "border-{$direction}-{$width}";
        }

        return $borderClass;
    }

    /**
     * Convert border radius class to directional border radius class
     */
    protected function convertBorderRadiusDirection($radiusClass, $direction): string
    {
        if (! $radiusClass) {
            return '';
        }

        // Handle different radius formats
        if ($radiusClass === 'rounded') {
            return "rounded-{$direction}";
        }

        if (str_starts_with($radiusClass, 'rounded-')) {
            // Extract the size part (e.g., 'sm', 'md', 'lg', etc.)
            $size = str_replace('rounded-', '', $radiusClass);

            // Special case for 'none'
            if ($size === 'none') {
                return "rounded-{$direction}-none";
            }

            return "rounded-{$direction}-{$size}";
        }

        return $radiusClass;
    }

    /**
     * Get box shadow CSS classes from properties
     */
    public function getBoxShadowCssClassesFromProperties(array $properties): array
    {
        $classes = [];

        $boxShadow = $properties['boxShadow'] ?? null;
        $boxShadowColor = $properties['boxShadowColor'] ?? null;
        $boxShadowOffsetX = $properties['boxShadowOffsetX'] ?? 0;
        $boxShadowOffsetY = $properties['boxShadowOffsetY'] ?? 0;
        $boxShadowBlur = $properties['boxShadowBlur'] ?? 0;
        $boxShadowSpread = $properties['boxShadowSpread'] ?? 0;
        $boxShadowInset = $properties['boxShadowInset'] ?? false;

        // Check if custom shadow values are used
        $hasCustomValues = $boxShadowOffsetX != 0 || $boxShadowOffsetY != 0 ||
                          $boxShadowBlur != 0 || $boxShadowSpread != 0 || $boxShadowInset;

        if ($hasCustomValues) {
            // Use custom shadow (will be handled in inline styles)
            $classes[] = 'shadow-custom';
        } elseif ($boxShadow) {
            // Use preset shadow
            $classes[] = $boxShadow;
        }

        // Add box shadow color class (only for non-hex colors and when using presets)
        if ($boxShadowColor && ! str_starts_with($boxShadowColor, '#') && ! $hasCustomValues) {
            $classes[] = "shadow-$boxShadowColor";
        }

        return $classes;
    }

    /**
     * Get box shadow inline styles for hex colors and custom values
     */
    public function getBoxShadowInlineStylesFromProperties(array $properties): array
    {
        $styles = [];

        $boxShadowColor = $properties['boxShadowColor'] ?? null;
        $boxShadowOffsetX = $properties['boxShadowOffsetX'] ?? 0;
        $boxShadowOffsetY = $properties['boxShadowOffsetY'] ?? 0;
        $boxShadowBlur = $properties['boxShadowBlur'] ?? 0;
        $boxShadowSpread = $properties['boxShadowSpread'] ?? 0;
        $boxShadowInset = $properties['boxShadowInset'] ?? false;

        // Check if custom shadow values are used
        $hasCustomValues = $boxShadowOffsetX != 0 || $boxShadowOffsetY != 0 ||
                          $boxShadowBlur != 0 || $boxShadowSpread != 0 || $boxShadowInset;

        if ($hasCustomValues) {
            // Build custom box-shadow value
            $shadowParts = [];

            if ($boxShadowInset) {
                $shadowParts[] = 'inset';
            }

            $shadowParts[] = $boxShadowOffsetX.'px';
            $shadowParts[] = $boxShadowOffsetY.'px';
            $shadowParts[] = $boxShadowBlur.'px';
            $shadowParts[] = $boxShadowSpread.'px';

            // Add color
            if ($boxShadowColor) {
                // Check if it's a hex color
                if (str_starts_with($boxShadowColor, '#')) {
                    $shadowParts[] = $boxShadowColor;
                } else {
                    $shadowParts[] = "var(--color-{$boxShadowColor})";
                }
            } else {
                $shadowParts[] = 'rgba(0, 0, 0, 0.1)'; // Default shadow color
            }

            $styles[] = 'box-shadow: '.implode(' ', $shadowParts);
        } elseif ($boxShadowColor && str_starts_with($boxShadowColor, '#')) {
            // Custom color for preset shadows
            $styles[] = "--tw-shadow-color: $boxShadowColor";
        }

        return $styles;
    }

    /**
     * Format a size value - if it's already a Tailwind class, return as-is,
     * otherwise convert numeric value to arbitrary value syntax
     */
    protected function formatSizeValue($value, $prefix): string
    {
        // If value is null or empty, return empty string
        if (! $value) {
            return '';
        }

        // Check if it's already a Tailwind class (starts with expected prefix)
        if (str_starts_with($value, $prefix.'-')) {
            return $value;
        }

        // Check if it's a numeric value (for backward compatibility)
        if (is_numeric($value)) {
            return $prefix.'-['.$value.'px]';
        }

        // Return as-is (might be a custom format)
        return $value;
    }
}
