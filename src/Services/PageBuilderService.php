<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Illuminate\Support\Str;

class PageBuilderService
{
    public function getAvailableBlocks()
    {
        $configBlocks = config('page-builder.blocks');

        $blocks = [];

        foreach ($configBlocks as $blockClass) {
            if (class_exists($blockClass)) {
                $instance = app($blockClass);
                $alias = $this->getClassAlias($blockClass);
                $blocks[] = [
                    'class' => $blockClass,
                    'alias' => $alias,
                    'label' => $instance->getPageBuilderLabel(),
                    'icon' => $instance->getPageBuilderIcon(),
                ];
            }
        }

        return $blocks;
    }

    public function getClassAlias($blockClass)
    {
        $alias = Str::kebab(str_replace('\\', '-', $blockClass));
        $alias = str_replace('--', '-', $alias);
        $alias = 'page-builder-' . $alias;
        return $alias;
    }


    /**
     * Get the properties for a block.
     *
     * @param string $blockClass The class name of the block.
     * @return array<BlockProperty> The properties for the block.
     */
    public function getBlockProperties($blockClass): array
    {
        $instance = app($blockClass);
        return $instance->getPageBuilderProperties();
    }

    /**
     * Get the properties for a block as an array.
     *
     * @param string $blockClass The class name of the block.
     * @return array The properties for the block.
     */
    public function getBlockPropertiesArray($blockClass): array
    {
        $properties = $this->getBlockProperties($blockClass);
        return array_map(function ($property) {
            return $property->toArray();
        }, $properties);
    }
}
