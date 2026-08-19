<?php

namespace Trinavo\LivewirePageBuilder\Tests\Fixtures;

use Trinavo\LivewirePageBuilder\Support\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;

/**
 * A block that does not opt into live edit, so it must never render a gear.
 */
class PlainBlock extends Block
{
    public $heading = 'Plain';

    public function getPageBuilderProperties(): array
    {
        return [
            new TextProperty('heading', 'Heading', defaultValue: $this->heading),
        ];
    }

    public function render()
    {
        return '<div>plain block</div>';
    }
}
