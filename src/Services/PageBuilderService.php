<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Illuminate\Support\Str;
use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Blocks\IconBlock;
use Trinavo\LivewirePageBuilder\Blocks\RichText;
use Trinavo\LivewirePageBuilder\Blocks\SimpleText;
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
                'category' => $instance->getPageBuilderCategory(),
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
        $blocks[] = SimpleText::class;
        $blocks[] = IconBlock::class;
        $blocks[] = RowBlock::class;

        return $blocks;
    }

    public function getConfigBlocksPages(): array
    {
        return config('page-builder.pages', []);
    }

    public function getAvailableLayouts(string $locale = 'en'): array
    {
        $layouts = [];
        foreach (config('page-builder.layouts', []) as $path) {
            if (! file_exists($path)) {
                continue;
            }

            $data = json_decode(file_get_contents($path), true);
            if (! is_array($data) || ! isset($data['components'])) {
                continue;
            }

            $meta = $data['meta'] ?? [];
            $nameMap = $meta['name'] ?? [];
            $descMap = $meta['description'] ?? [];

            $layouts[] = [
                'path' => $path,
                'name' => $nameMap[$locale] ?? $nameMap['en'] ?? basename($path),
                'description' => $descMap[$locale] ?? $descMap['en'] ?? '',
            ];
        }

        return $layouts;
    }

    public function registerBlocks(): void
    {
        foreach ($this->getConfigBlocks() as $blockClass) {
            $alias = $this->getClassAlias($blockClass);
            Livewire::component($alias, $blockClass);
        }
    }

    public function isBlockAliasRegistered(string $alias): bool
    {
        if ($alias === 'builder-page-block' || $alias === 'row-block') {
            return true;
        }

        if (str_contains($alias, 'row-block')) {
            return true;
        }

        return (bool) $this->getClassNameFromAlias($alias);
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
        $flex = $properties['flex'] ?? 'row';  // Default to 'row' to match RowBlock default
        if ($flex && $flex !== 'none') {
            $classes[] = "flex flex-{$flex}";
        }

        // Add flex-wrap property
        $flexWrap = $properties['flexWrap'] ?? '';
        if ($flexWrap && $flexWrap !== '' && $flex && $flex !== 'none') {
            $classes[] = "flex-{$flexWrap}";
        }

        // Add justify-content property (takes precedence over old contentAlign mapping)
        $justifyContent = $properties['justifyContent'] ?? '';
        if ($justifyContent && $justifyContent !== '' && $flex && $flex !== 'none') {
            $classes[] = $justifyContent;
        } else {
            // Fallback to old contentAlign for backward compatibility
            $contentAlign = $properties['contentAlign'] ?? 'content-center';
            if ($flex && $flex !== 'none') {  // Only add vertical alignment when flex is active
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
        }

        // Add align-items property
        $alignItems = $properties['alignItems'] ?? '';
        if ($alignItems && $alignItems !== '' && $flex && $flex !== 'none') {
            $classes[] = $alignItems;
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
            $classes[] = '@7xl:'.$contentWidthDesktop;
        }

        if ($mobileGap) {
            $classes[] = "gap-$mobileGap";
        }

        if ($tabletGap) {
            $classes[] = "@3xl:gap-$tabletGap";
        }

        if ($desktopGap) {
            $classes[] = "@7xl:gap-$desktopGap";
        }

        $classes[] = 'h-full';

        return implode(' ', $classes);
    }

    public function getCssClassesFromProperties(array $properties, bool $isRow = false): ?string
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
        $mobileMarginTop = (int) ($properties['mobileMarginTop'] ?? 0);
        $mobileMarginRight = (int) ($properties['mobileMarginRight'] ?? 0);
        $mobileMarginBottom = (int) ($properties['mobileMarginBottom'] ?? 0);
        $mobileMarginLeft = (int) ($properties['mobileMarginLeft'] ?? 0);

        $tabletMarginTop = (int) ($properties['tabletMarginTop'] ?? 0);
        $tabletMarginRight = (int) ($properties['tabletMarginRight'] ?? 0);
        $tabletMarginBottom = (int) ($properties['tabletMarginBottom'] ?? 0);
        $tabletMarginLeft = (int) ($properties['tabletMarginLeft'] ?? 0);

        $desktopMarginTop = (int) ($properties['desktopMarginTop'] ?? 0);
        $desktopMarginRight = (int) ($properties['desktopMarginRight'] ?? 0);
        $desktopMarginBottom = (int) ($properties['desktopMarginBottom'] ?? 0);
        $desktopMarginLeft = (int) ($properties['desktopMarginLeft'] ?? 0);

        $selfCentered = $properties['selfCentered'] ?? false;
        $textColor = $properties['textColor'] ?? null;
        $backgroundColor = $properties['backgroundColor'] ?? null;

        $classes = [];

        if ($hiddenMobile && $hiddenTablet && $hiddenDesktop) {
            $classes[] = 'hidden';
        } elseif ($hiddenMobile && $hiddenTablet) {
            $classes[] = 'hidden @7xl:block';
        } elseif ($hiddenMobile && $hiddenDesktop) {
            $classes[] = 'hidden @3xl:block @7xl:hidden';
        } elseif ($hiddenTablet && $hiddenDesktop) {
            $classes[] = 'block @3xl:hidden';
        } elseif ($hiddenMobile) {
            $classes[] = 'hidden @3xl:block';
        } elseif ($hiddenTablet) {
            $classes[] = 'block @3xl:hidden @7xl:block';
        } elseif ($hiddenDesktop) {
            $classes[] = 'block @7xl:hidden';
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
        if ($tabletPaddingTop != $mobilePaddingTop) {
            $classes[] = $tabletPaddingTop > 0
                ? "@3xl:pt-$tabletPaddingTop"
                : '@3xl:pt-0';
        }
        if ($tabletPaddingRight != $mobilePaddingRight) {
            $classes[] = $tabletPaddingRight > 0
                ? "@3xl:pr-$tabletPaddingRight"
                : '@3xl:pr-0';
        }
        if ($tabletPaddingBottom != $mobilePaddingBottom) {
            $classes[] = $tabletPaddingBottom > 0
                ? "@3xl:pb-$tabletPaddingBottom"
                : '@3xl:pb-0';
        }
        if ($tabletPaddingLeft != $mobilePaddingLeft) {
            $classes[] = $tabletPaddingLeft > 0
                ? "@3xl:pl-$tabletPaddingLeft"
                : '@3xl:pl-0';
        }

        // Desktop padding (only if different from tablet)
        if ($desktopPaddingTop != $tabletPaddingTop) {
            $classes[] = $desktopPaddingTop > 0
                ? "@7xl:pt-$desktopPaddingTop"
                : '@7xl:pt-0';
        }
        if ($desktopPaddingRight != $tabletPaddingRight) {
            $classes[] = $desktopPaddingRight > 0
                ? "@7xl:pr-$desktopPaddingRight"
                : '@7xl:pr-0';
        }
        if ($desktopPaddingBottom != $tabletPaddingBottom) {
            $classes[] = $desktopPaddingBottom > 0
                ? "@7xl:pb-$desktopPaddingBottom"
                : '@7xl:pb-0';
        }
        if ($desktopPaddingLeft != $tabletPaddingLeft) {
            $classes[] = $desktopPaddingLeft > 0
                ? "@7xl:pl-$desktopPaddingLeft"
                : '@7xl:pl-0';
        }

        // Add responsive margin classes
        // Mobile margin
        if ($mobileMarginTop != 0) {
            $classes[] = $mobileMarginTop < 0 ? '-mt-'.abs($mobileMarginTop) : "mt-$mobileMarginTop";
        }
        if ($mobileMarginRight != 0) {
            $classes[] = $mobileMarginRight < 0 ? '-mr-'.abs($mobileMarginRight) : "mr-$mobileMarginRight";
        }
        if ($mobileMarginBottom != 0) {
            $classes[] = $mobileMarginBottom < 0 ? '-mb-'.abs($mobileMarginBottom) : "mb-$mobileMarginBottom";
        }
        if ($mobileMarginLeft != 0) {
            $classes[] = $mobileMarginLeft < 0 ? '-ml-'.abs($mobileMarginLeft) : "ml-$mobileMarginLeft";
        }

        // Tablet margin (only if different from mobile)
        if ($tabletMarginTop != $mobileMarginTop) {
            $classes[] = $tabletMarginTop === 0
                ? '@3xl:mt-0'
                : ($tabletMarginTop < 0 ? '@3xl:-mt-'.abs($tabletMarginTop) : "@3xl:mt-$tabletMarginTop");
        }
        if ($tabletMarginRight != $mobileMarginRight) {
            $classes[] = $tabletMarginRight === 0
                ? '@3xl:mr-0'
                : ($tabletMarginRight < 0 ? '@3xl:-mr-'.abs($tabletMarginRight) : "@3xl:mr-$tabletMarginRight");
        }
        if ($tabletMarginBottom != $mobileMarginBottom) {
            $classes[] = $tabletMarginBottom === 0
                ? '@3xl:mb-0'
                : ($tabletMarginBottom < 0 ? '@3xl:-mb-'.abs($tabletMarginBottom) : "@3xl:mb-$tabletMarginBottom");
        }
        if ($tabletMarginLeft != $mobileMarginLeft) {
            $classes[] = $tabletMarginLeft === 0
                ? '@3xl:ml-0'
                : ($tabletMarginLeft < 0 ? '@3xl:-ml-'.abs($tabletMarginLeft) : "@3xl:ml-$tabletMarginLeft");
        }

        // Desktop margin (only if different from tablet)
        if ($desktopMarginTop != $tabletMarginTop) {
            $classes[] = $desktopMarginTop === 0
                ? '@7xl:mt-0'
                : ($desktopMarginTop < 0 ? '@7xl:-mt-'.abs($desktopMarginTop) : "@7xl:mt-$desktopMarginTop");
        }
        if ($desktopMarginRight != $tabletMarginRight) {
            $classes[] = $desktopMarginRight === 0
                ? '@7xl:mr-0'
                : ($desktopMarginRight < 0 ? '@7xl:-mr-'.abs($desktopMarginRight) : "@7xl:mr-$desktopMarginRight");
        }
        if ($desktopMarginBottom != $tabletMarginBottom) {
            $classes[] = $desktopMarginBottom === 0
                ? '@7xl:mb-0'
                : ($desktopMarginBottom < 0 ? '@7xl:-mb-'.abs($desktopMarginBottom) : "@7xl:mb-$desktopMarginBottom");
        }
        if ($desktopMarginLeft != $tabletMarginLeft) {
            $classes[] = $desktopMarginLeft === 0
                ? '@7xl:ml-0'
                : ($desktopMarginLeft < 0 ? '@7xl:-ml-'.abs($desktopMarginLeft) : "@7xl:ml-$desktopMarginLeft");
        }

        if ($textColor) {
            if (! str_starts_with($textColor, '#') && ! str_starts_with($textColor, 'rgb')) {
                $classes[] = "text-$textColor";
            }
        }

        // Add background color classes or inline styles for hex and rgba colors
        if ($backgroundColor) {
            if (! str_starts_with($backgroundColor, '#') && ! str_starts_with($backgroundColor, 'rgb')) {
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

        // Add backdrop filter classes
        $backdropFilterClasses = $this->getBackdropFilterCssClassesFromProperties($properties);
        if (count($backdropFilterClasses) > 0) {
            $classes = array_merge($classes, $backdropFilterClasses);
        }

        // Add filter classes
        $filterClasses = $this->getFilterCssClassesFromProperties($properties);
        if (count($filterClasses) > 0) {
            $classes = array_merge($classes, $filterClasses);
        }

        // Vertical alignment - allow customization instead of hardcoded centering
        $contentAlign = $properties['contentAlign'] ?? 'content-center';
        $classes[] = $contentAlign;        // Add position property
        $position = $properties['position'] ?? null;
        if ($position) {
            $classes[] = $position;
        }

        // Add z-index property
        $zIndex = $properties['zIndex'] ?? null;
        if ($zIndex) {
            $classes[] = $zIndex;
        }

        // Add text align property
        $textAlign = $properties['textAlign'] ?? null;
        if ($textAlign) {
            $classes[] = $textAlign;
        }

        $classString = implode(' ', array_unique($classes));

        $heightClasses = $this->getHeightCssClassesFromProperties($properties);
        if (trim($heightClasses) !== '') {
            $classString .= ' '.$heightClasses;
        }

        $widthClasses = $this->getWidthCssClassesFromProperties($properties, isRow: $isRow);
        if (count($widthClasses) > 0) {
            $classString .= ' '.implode(' ', $widthClasses);
        }

        $transformClasses = $this->getTransformCssClassesFromProperties($properties);
        if (trim($transformClasses) !== '') {
            $classString .= ' '.$transformClasses;
        }

        $fontSizeClasses = $this->getFontSizeCssClassesFromProperties($properties);
        if (trim($fontSizeClasses) !== '') {
            $classString .= ' '.$fontSizeClasses;
        }

        return $classString;
    }

    public function getWidthCssClassesFromProperties(array $properties, bool $isRow = false): array
    {
        $mobileWidth = $properties['mobileWidth'] ?? ($isRow ? 'w-full' : 'w-auto');
        $tabletWidth = $properties['tabletWidth'] ?? ($isRow ? 'w-full' : 'w-auto');
        $desktopWidth = $properties['desktopWidth'] ?? ($isRow ? 'w-full' : 'w-auto');

        $classes = [];

        // Format width values (in case they are custom arbitrary values or classes)
        $classes[] = $this->formatSizeValue($mobileWidth, 'w');

        // Only add tablet/desktop widths if they're different from mobile
        if ($tabletWidth !== $mobileWidth) {
            $classes[] = '@3xl:'.$this->formatSizeValue($tabletWidth, 'w');
        }

        if ($desktopWidth !== $tabletWidth) {
            $classes[] = '@7xl:'.$this->formatSizeValue($desktopWidth, 'w');
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
        // Add text color classes or inline styles for hex and rgba colors
        if ($textColor) {
            if (str_starts_with($textColor, '#') || str_starts_with($textColor, 'rgb')) {
                $styles[] = "color: $textColor";
            }
        }

        // Add background color classes or inline styles for hex and rgba colors
        if ($backgroundColor) {
            if (str_starts_with($backgroundColor, '#') || str_starts_with($backgroundColor, 'rgb')) {
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

    /**
     * Get data attributes from properties (for DaisyUI themes, etc.)
     */
    public function getDataAttributesFromProperties(array $properties): string
    {
        $attributes = [];

        $forceDarkMode = $properties['forceDarkMode'] ?? false;

        if ($forceDarkMode) {
            $attributes[] = 'data-theme="dark"';
        }

        return implode(' ', $attributes);
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
            $classes[] = '@7xl:'.$this->formatSizeValue($desktopHeight, 'h');
        }

        // Handle min-height values
        if ($mobileMinHeight) {
            $classes[] = $this->formatSizeValue($mobileMinHeight, 'min-h');
        }

        if ($tabletMinHeight && $tabletMinHeight !== $mobileMinHeight) {
            $classes[] = '@3xl:'.$this->formatSizeValue($tabletMinHeight, 'min-h');
        }

        if ($desktopMinHeight && $desktopMinHeight !== $tabletMinHeight) {
            $classes[] = '@7xl:'.$this->formatSizeValue($desktopMinHeight, 'min-h');
        }

        return implode(' ', $classes);
    }

    /**
     * Get font size CSS classes from properties
     */
    public function getFontSizeCssClassesFromProperties(array $properties): string
    {
        $mobileFontSize = $properties['mobileFontSize'] ?? null;
        $tabletFontSize = $properties['tabletFontSize'] ?? null;
        $desktopFontSize = $properties['desktopFontSize'] ?? null;

        $classes = [];

        if ($mobileFontSize) {
            $classes[] = $mobileFontSize;
        }

        if ($tabletFontSize && $tabletFontSize !== $mobileFontSize) {
            $classes[] = '@3xl:'.$tabletFontSize;
        }

        if ($desktopFontSize && $desktopFontSize !== $tabletFontSize) {
            $classes[] = '@7xl:'.$desktopFontSize;
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

        // Add border color classes (only for Tailwind color names, not hex or rgba)
        if ($borderColor && ! str_starts_with($borderColor, '#') && ! str_starts_with($borderColor, 'rgb')) {
            $classes[] = "border-$borderColor";
        } else {
            // Individual border colors (only for Tailwind color names, not hex or rgba)
            if ($borderTopColor && ! str_starts_with($borderTopColor, '#') && ! str_starts_with($borderTopColor, 'rgb')) {
                $classes[] = "border-t-$borderTopColor";
            }
            if ($borderRightColor && ! str_starts_with($borderRightColor, '#') && ! str_starts_with($borderRightColor, 'rgb')) {
                $classes[] = "border-r-$borderRightColor";
            }
            if ($borderBottomColor && ! str_starts_with($borderBottomColor, '#') && ! str_starts_with($borderBottomColor, 'rgb')) {
                $classes[] = "border-b-$borderBottomColor";
            }
            if ($borderLeftColor && ! str_starts_with($borderLeftColor, '#') && ! str_starts_with($borderLeftColor, 'rgb')) {
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

        // Add border color styles for hex and rgba colors
        if ($borderColor && (str_starts_with($borderColor, '#') || str_starts_with($borderColor, 'rgb'))) {
            $styles[] = "border-color: $borderColor";
        } else {
            // Individual border colors for hex and rgba values
            if ($borderTopColor && (str_starts_with($borderTopColor, '#') || str_starts_with($borderTopColor, 'rgb'))) {
                $styles[] = "border-top-color: $borderTopColor";
            }
            if ($borderRightColor && (str_starts_with($borderRightColor, '#') || str_starts_with($borderRightColor, 'rgb'))) {
                $styles[] = "border-right-color: $borderRightColor";
            }
            if ($borderBottomColor && (str_starts_with($borderBottomColor, '#') || str_starts_with($borderBottomColor, 'rgb'))) {
                $styles[] = "border-bottom-color: $borderBottomColor";
            }
            if ($borderLeftColor && (str_starts_with($borderLeftColor, '#') || str_starts_with($borderLeftColor, 'rgb'))) {
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
     * Get backdrop filter CSS classes from properties
     */
    public function getBackdropFilterCssClassesFromProperties(array $properties): array
    {
        $classes = [];

        $backdropBlur = $properties['backdropBlur'] ?? null;

        if ($backdropBlur) {
            $classes[] = $backdropBlur;
        }

        return $classes;
    }

    /**
     * Get filter CSS classes from properties
     */
    public function getFilterCssClassesFromProperties(array $properties): array
    {
        $classes = [];

        $blur = $properties['blur'] ?? null;
        $dropShadow = $properties['dropShadow'] ?? null;

        if ($blur) {
            $classes[] = $blur;
        }

        if ($dropShadow) {
            $classes[] = $dropShadow;
        }

        return $classes;
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

    /**
     * Get transform CSS classes from properties
     */
    public function getTransformCssClassesFromProperties(array $properties): string
    {
        $classes = [];

        // Rotate
        $mobileRotate = $properties['mobileRotate'] ?? 0;
        $tabletRotate = $properties['tabletRotate'] ?? 0;
        $desktopRotate = $properties['desktopRotate'] ?? 0;

        // Scale
        $mobileScale = $properties['mobileScale'] ?? 1;
        $tabletScale = $properties['tabletScale'] ?? 1;
        $desktopScale = $properties['desktopScale'] ?? 1;

        // Translate X
        $mobileTranslateX = $properties['mobileTranslateX'] ?? 0;
        $tabletTranslateX = $properties['tabletTranslateX'] ?? 0;
        $desktopTranslateX = $properties['desktopTranslateX'] ?? 0;

        // Translate Y
        $mobileTranslateY = $properties['mobileTranslateY'] ?? 0;
        $tabletTranslateY = $properties['tabletTranslateY'] ?? 0;
        $desktopTranslateY = $properties['desktopTranslateY'] ?? 0;

        // Skew X
        $mobileSkewX = $properties['mobileSkewX'] ?? 0;
        $tabletSkewX = $properties['tabletSkewX'] ?? 0;
        $desktopSkewX = $properties['desktopSkewX'] ?? 0;

        // Skew Y
        $mobileSkewY = $properties['mobileSkewY'] ?? 0;
        $tabletSkewY = $properties['tabletSkewY'] ?? 0;
        $desktopSkewY = $properties['desktopSkewY'] ?? 0;

        // Add rotate classes (mobile first)
        if ($mobileRotate != 0) {
            $classes[] = $this->formatTransformValue(value: $mobileRotate, prefix: 'rotate');
        }
        if ($tabletRotate != $mobileRotate) {
            $classes[] = '@3xl:'.$this->formatTransformValue(value: $tabletRotate, prefix: 'rotate');
        }
        if ($desktopRotate != $tabletRotate) {
            $classes[] = '@7xl:'.$this->formatTransformValue(value: $desktopRotate, prefix: 'rotate');
        }

        // Add scale classes
        if ($mobileScale != 1) {
            $classes[] = $this->formatTransformValue(value: $mobileScale, prefix: 'scale', multiplier: 100);
        }
        if ($tabletScale != $mobileScale) {
            $classes[] = '@3xl:'.$this->formatTransformValue(value: $tabletScale, prefix: 'scale', multiplier: 100);
        }
        if ($desktopScale != $tabletScale) {
            $classes[] = '@7xl:'.$this->formatTransformValue(value: $desktopScale, prefix: 'scale', multiplier: 100);
        }

        // Add translate-x classes
        if ($mobileTranslateX != 0) {
            $classes[] = $this->formatTransformValue(value: $mobileTranslateX, prefix: 'translate-x');
        }
        if ($tabletTranslateX != $mobileTranslateX) {
            $classes[] = '@3xl:'.$this->formatTransformValue(value: $tabletTranslateX, prefix: 'translate-x');
        }
        if ($desktopTranslateX != $tabletTranslateX) {
            $classes[] = '@7xl:'.$this->formatTransformValue(value: $desktopTranslateX, prefix: 'translate-x');
        }

        // Add translate-y classes
        if ($mobileTranslateY != 0) {
            $classes[] = $this->formatTransformValue(value: $mobileTranslateY, prefix: 'translate-y');
        }
        if ($tabletTranslateY != $mobileTranslateY) {
            $classes[] = '@3xl:'.$this->formatTransformValue(value: $tabletTranslateY, prefix: 'translate-y');
        }
        if ($desktopTranslateY != $tabletTranslateY) {
            $classes[] = '@7xl:'.$this->formatTransformValue(value: $desktopTranslateY, prefix: 'translate-y');
        }

        // Add skew-x classes
        if ($mobileSkewX != 0) {
            $classes[] = $this->formatTransformValue(value: $mobileSkewX, prefix: 'skew-x');
        }
        if ($tabletSkewX != $mobileSkewX) {
            $classes[] = '@3xl:'.$this->formatTransformValue(value: $tabletSkewX, prefix: 'skew-x');
        }
        if ($desktopSkewX != $tabletSkewX) {
            $classes[] = '@7xl:'.$this->formatTransformValue(value: $desktopSkewX, prefix: 'skew-x');
        }

        // Add skew-y classes
        if ($mobileSkewY != 0) {
            $classes[] = $this->formatTransformValue(value: $mobileSkewY, prefix: 'skew-y');
        }
        if ($tabletSkewY != $mobileSkewY) {
            $classes[] = '@3xl:'.$this->formatTransformValue(value: $tabletSkewY, prefix: 'skew-y');
        }
        if ($desktopSkewY != $tabletSkewY) {
            $classes[] = '@7xl:'.$this->formatTransformValue(value: $desktopSkewY, prefix: 'skew-y');
        }

        return implode(' ', $classes);
    }

    /**
     * Format transform value to Tailwind class
     */
    protected function formatTransformValue(float|int $value, string $prefix, ?int $multiplier = null): string
    {
        // Handle zero value
        if ($value == 0) {
            return $prefix.'-0';
        }

        // Apply multiplier if provided (for scale: 1.5 becomes 150)
        if ($multiplier !== null) {
            $value = $value * $multiplier;
        }

        // Handle negative values
        $isNegative = $value < 0;
        $absValue = abs($value);

        // For scale, check common values (50, 75, 90, 95, 100, 105, 110, 125, 150)
        if ($prefix === 'scale') {
            $commonScales = [0, 50, 75, 90, 95, 100, 105, 110, 125, 150];
            if (in_array($absValue, $commonScales)) {
                return $prefix.'-'.$absValue;
            }

            // Use arbitrary value for custom scale
            return $prefix.'-['.$absValue.']';
        }

        // For rotate and skew, common angles: 0, 1, 2, 3, 6, 12, 45, 90, 180
        if (in_array($prefix, ['rotate', 'skew-x', 'skew-y'])) {
            $commonAngles = [0, 1, 2, 3, 6, 12, 45, 90, 180];
            if (in_array($absValue, $commonAngles)) {
                return ($isNegative ? '-' : '').$prefix.'-'.$absValue;
            }

            // Use arbitrary value for custom angle
            return ($isNegative ? '-' : '').$prefix.'-['.$absValue.'deg]';
        }

        // For translate, check if it's a common spacing value (0.5rem increments)
        // Common translate values: 0, 0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 5, 6, 7, 8, 9, 10, 11, 12, 14, 16, 20, 24, 28, 32, 36, 40, 44, 48, 52, 56, 60, 64, 72, 80, 96
        $commonTranslate = [0, 0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 5, 6, 7, 8, 9, 10, 11, 12, 14, 16, 20, 24, 28, 32, 36, 40, 44, 48, 52, 56, 60, 64, 72, 80, 96];
        if (in_array($absValue, $commonTranslate)) {
            return ($isNegative ? '-' : '').$prefix.'-'.$absValue;
        }

        // Use arbitrary value with px unit for custom translate
        return ($isNegative ? '-' : '').$prefix.'-['.$absValue.'px]';
    }
}
