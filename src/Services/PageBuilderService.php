<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Illuminate\Support\Str;
use Livewire\Livewire;
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
        return config('page-builder.blocks', []);
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
        foreach ($this->getConfigBlocks() as $blockClass) {
            if ($this->getClassAlias($blockClass) === $alias) {
                return $blockClass;
            }
        }

        return null;
    }
}
