<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Illuminate\Support\Str;
use Livewire\Livewire;
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
        $hiddenMobile = $properties['hidden_mobile'] ?? false;
        $hiddenTablet = $properties['hidden_tablet'] ?? false;
        $hiddenDesktop = $properties['hidden_desktop'] ?? false;
        $mobileGridSize = $properties['mobile_grid_size'] ?? 12;
        $tabletGridSize = $properties['tablet_grid_size'] ?? 12;
        $desktopGridSize = $properties['desktop_grid_size'] ?? 12;
        $fullWidth = $properties['full_width'] ?? false;
        // Padding properties
        $paddingTop = $properties['padding_top'] ?? 0;
        $paddingRight = $properties['padding_right'] ?? 0;
        $paddingBottom = $properties['padding_bottom'] ?? 0;
        $paddingLeft = $properties['padding_left'] ?? 0;

        // Margin properties
        $marginTop = $properties['margin_top'] ?? 0;
        $marginRight = $properties['margin_right'] ?? 0;
        $marginBottom = $properties['margin_bottom'] ?? 0;
        $marginLeft = $properties['margin_left'] ?? 0;

        // Layout properties
        $useContainer = $properties['use_container'] ?? false;
        $selfCentered = $properties['self_centered'] ?? false;
        $gridColumns = $properties['grid_columns'] ?? null;
        $flex = $properties['flex'] ?? false;
        $textColor = $properties['text_color'] ?? null;
        $backgroundColor = $properties['background_color'] ?? null;

        $classes = [];

        // Container query classes
        if ($hiddenMobile && $hiddenTablet && $hiddenDesktop) {
            $classes[] = 'hidden';
        } elseif ($hiddenMobile && $hiddenTablet) {
            $classes[] = 'hidden @md:block';
        } elseif ($hiddenMobile && $hiddenDesktop) {
            $classes[] = 'hidden @sm:block @lg:hidden';
        } elseif ($hiddenTablet && $hiddenDesktop) {
            $classes[] = 'block @md:hidden';
        } elseif ($hiddenMobile) {
            $classes[] = 'hidden @sm:block';
        } elseif ($hiddenTablet) {
            $classes[] = 'block @md:hidden @lg:block';
        } elseif ($hiddenDesktop) {
            $classes[] = 'block @lg:hidden';
        } else {
            $classes[] = 'block';
        }

        if ($useContainer) {
            $classes[] = 'container';
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

        if ($fullWidth) {
            $classes[] = 'w-full';
        }

        if ($flex) {
            $classes[] = 'flex';
        } elseif ($gridColumns) {
            $classes[] = "grid grid-cols-$gridColumns";
        }

        if ($mobileGridSize) {
            $classes[] = "col-span-$mobileGridSize";
        }

        if ($tabletGridSize) {
            $classes[] = "@md:col-span-$tabletGridSize";
        }

        if ($desktopGridSize) {
            $classes[] = "@lg:col-span-$desktopGridSize";
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

        $classString = implode(' ', array_unique($classes));

        return $classString;
    }

    public function getInlineStylesFromProperties(array $properties): ?string
    {
        $textColor = $properties['text_color'] ?? null;
        $backgroundColor = $properties['background_color'] ?? null;
        $backgroundImage = $properties['background_image'] ?? null;
        $backgroundPosition = $properties['background_position'] ?? 'center';
        $backgroundSize = $properties['background_size'] ?? 'cover';
        $backgroundRepeat = $properties['background_repeat'] ?? 'no-repeat';

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

        return implode(';', $styles);
    }
}
