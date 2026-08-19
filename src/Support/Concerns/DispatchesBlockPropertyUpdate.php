<?php

namespace Trinavo\LivewirePageBuilder\Support\Concerns;

/**
 * Shared by the block-properties.* widgets so they can address their property updates.
 *
 * The builder relies on `updateBlockProperty` being global: PageEditor, RowBlock and
 * BuilderBlock all listen for it. On a public page that is wasteful, because every
 * nested row on the page also listens and would be pulled into each keystroke. Setting
 * $updateTarget makes the dispatch component-scoped (Livewire fires it non-bubbling on
 * the target's element only), so the live edit sheet is the sole recipient.
 */
trait DispatchesBlockPropertyUpdate
{
    /**
     * Livewire component name/class to address property updates to.
     * Null keeps the historical global dispatch, which is what the builder needs.
     */
    public ?string $updateTarget = null;

    /**
     * Emit a property change for the row/block this widget was mounted for.
     *
     * @param  mixed  $value
     */
    protected function dispatchBlockPropertyUpdate(string $propertyName, $value): void
    {
        $event = $this->dispatch(
            'updateBlockProperty',
            $this->rowId,
            $this->blockId,
            $propertyName,
            $value
        );

        if ($this->updateTarget) {
            $event->to($this->updateTarget);
        }
    }
}
