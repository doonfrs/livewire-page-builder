<?php

namespace Trinavo\LivewirePageBuilder\Tests\Fixtures;

use Trinavo\LivewirePageBuilder\Support\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;

/**
 * A live editable block that can have nothing to show, the way a currency menu does on a
 * store with one currency. Whether it shows anything is its own business: $shown here.
 */
class HiddenContentBlock extends Block
{
    public $title = 'Hello';

    public bool $shown = false;

    public function getPageBuilderProperties(): array
    {
        return [
            new TextProperty('title', 'Title', defaultValue: $this->title),
        ];
    }

    public function getPageBuilderLiveEditProperties(): array
    {
        return ['title'];
    }

    public function hasVisibleContent(): bool
    {
        return $this->shown;
    }

    public function render()
    {
        return <<<'BLADE'
        <div>
            @if ($shown)
                <span>{{ $title }}</span>
            @endif
        </div>
        BLADE;
    }
}
