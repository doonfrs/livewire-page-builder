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
}
