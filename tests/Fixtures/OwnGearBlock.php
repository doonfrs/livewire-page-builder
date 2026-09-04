<?php

namespace Trinavo\LivewirePageBuilder\Tests\Fixtures;

use Trinavo\LivewirePageBuilder\Support\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;

/**
 * A live editable block that draws the gear itself, the way a host's header or centred
 * menu does: its content sits well inside the wrapper, so the wrapper's corner is nowhere
 * near what the gear edits.
 */
class OwnGearBlock extends Block
{
    public $title = 'Hello';

    public function getPageBuilderProperties(): array
    {
        return [
            new TextProperty('title', 'Title', defaultValue: $this->title),
        ];
    }

    public function getPageBuilderLiveEditProperties(): array
    {
        return $this->getPageBuilderProperties();
    }

    public function drawsOwnLiveEditGear(): bool
    {
        return true;
    }

    public function render()
    {
        return <<<'BLADE'
        <div>
            <span class="own-gear-slot">
                <x-page-builder::live-edit-gear :ctx="$liveEditContext" :floating="false" />
            </span>
        </div>
        BLADE;
    }
}
