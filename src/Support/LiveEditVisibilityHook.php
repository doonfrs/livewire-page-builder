<?php

namespace Trinavo\LivewirePageBuilder\Support;

use Livewire\ComponentHook;
use Livewire\Drawer\Utils;
use Trinavo\LivewirePageBuilder\Services\PageBuilderUIService;

/**
 * Marks the root of a block that rendered nothing visible, so its edit gears can hide.
 *
 * The gear lives in the block's wrapper, which is rendered before the block itself, so
 * the wrapper cannot ask the block. Instead the block answers Block::hasVisibleContent()
 * after it has rendered, and this stamps `data-pb-hidden` on its root. The live edit
 * mount carries the one CSS rule that hides any `data-pb-edit-gear` tied to that root.
 *
 * Runs on every render, Livewire updates included, so a block that gains content gets
 * its gear back without a reload, and a lazy block loses it once its placeholder goes.
 */
class LiveEditVisibilityHook extends ComponentHook
{
    public function render($view, $data)
    {
        return function ($html, $replaceHtml) {
            $block = $this->component;

            // Cheapest first: guests stop at the live edit check, and a block keeping the
            // default never reaches its own logic.
            if (! $block instanceof Block || $block->editMode) {
                return;
            }

            if (! app(PageBuilderUIService::class)->isLiveEditEnabled()) {
                return;
            }

            if ($block->hasVisibleContent()) {
                return;
            }

            $replaceHtml(Utils::insertAttributesIntoHtmlRoot($html, ['data-pb-hidden' => '']));
        };
    }
}
