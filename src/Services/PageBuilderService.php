<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Illuminate\Support\Str;
use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Blocks\RichText;
use Trinavo\LivewirePageBuilder\Blocks\Section;
use Trinavo\LivewirePageBuilder\Http\Livewire\BuilderPageBlock;

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
        $blocks[] = Section::class;
        $blocks[] = RichText::class;

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

    public function getCssClassesFromProperties(array $properties, bool $isRowBlock = false): ?string
    {
        $hiddenMobile = $properties['hiddenMobile'] ?? false;
        $hiddenTablet = $properties['hiddenTablet'] ?? false;
        $hiddenDesktop = $properties['hiddenDesktop'] ?? false;
        $mobileWidth = $properties['mobileWidth'] ?? 'w-auto';
        $tabletWidth = $properties['tabletWidth'] ?? 'w-auto';
        $desktopWidth = $properties['desktopWidth'] ?? 'w-auto';

        // Padding properties
        $paddingTop = $properties['paddingTop'] ?? 0;
        $paddingRight = $properties['paddingRight'] ?? 0;
        $paddingBottom = $properties['paddingBottom'] ?? 0;
        $paddingLeft = $properties['paddingLeft'] ?? 0;

        // Margin properties
        $marginTop = $properties['marginTop'] ?? 0;
        $marginRight = $properties['marginRight'] ?? 0;
        $marginBottom = $properties['marginBottom'] ?? 0;
        $marginLeft = $properties['marginLeft'] ?? 0;

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

        // Add padding classes
        if ($paddingTop > 0) {
            $classes[] = "pt-$paddingTop";
        }
        if ($paddingRight > 0) {
            $classes[] = "pr-$paddingRight";
        }
        if ($paddingBottom > 0) {
            $classes[] = "pb-$paddingBottom";
        }
        if ($paddingLeft > 0) {
            $classes[] = "pl-$paddingLeft";
        }

        // Add margin classes
        if ($marginTop > 0) {
            $classes[] = "mt-$marginTop";
        }
        if ($marginRight > 0) {
            $classes[] = "mr-$marginRight";
        }
        if ($marginBottom > 0) {
            $classes[] = "mb-$marginBottom";
        }
        if ($marginLeft > 0) {
            $classes[] = "ml-$marginLeft";
        }

        $classes[] = $mobileWidth;

        // Only add tablet/desktop widths if they're different from mobile
        if ($tabletWidth !== $mobileWidth) {
            $classes[] = '@3xl:'.$tabletWidth;
        }

        if ($desktopWidth !== $tabletWidth) {
            $classes[] = '@5xl:'.$desktopWidth;
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

        $classes[] = 'items-center';
        $classes[] = 'content-center';

        $classString = implode(' ', array_unique($classes));

        $heightClasses = $this->getHeightCssClassesFromProperties($properties);
        if (trim($heightClasses) !== '') {
            $classString .= ' '.$heightClasses;
        }

        return $classString;
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
        if ($mobileHeight) {
            $classes[] = 'h-['.$mobileHeight.'px]';
        }

        // Only add tablet/desktop heights if they're different from mobile
        if ($tabletHeight && $tabletHeight !== $mobileHeight) {
            $classes[] = '@3xl:h-['.$tabletHeight.'px]';
        }

        if ($desktopHeight && $desktopHeight !== $tabletHeight) {
            $classes[] = '@5xl:h-['.$desktopHeight.'px]';
        }

        if ($mobileMinHeight) {
            $classes[] = 'min-h-['.$mobileMinHeight.'px]';
        }

        if ($tabletMinHeight && $mobileMinHeight && $tabletMinHeight !== $mobileMinHeight) {
            $classes[] = '@3xl:min-h-['.$tabletMinHeight.'px]';
        }

        if ($desktopMinHeight && $tabletMinHeight && $desktopMinHeight !== $tabletMinHeight) {
            $classes[] = '@5xl:min-h-['.$desktopMinHeight.'px]';
        }

        return implode(' ', $classes);
    }
}
