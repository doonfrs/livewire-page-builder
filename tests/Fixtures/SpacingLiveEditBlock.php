<?php

namespace Trinavo\LivewirePageBuilder\Tests\Fixtures;

use Trinavo\LivewirePageBuilder\Support\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\ResponsiveSpacingProperty;

/**
 * Live edit block whose declaration includes a responsive spacing property, which
 * expands into one stored key per device/direction.
 */
class SpacingLiveEditBlock extends Block
{
    public function getPageBuilderLiveEditProperties(): array
    {
        return [
            new ResponsiveSpacingProperty('padding', 'Padding'),
        ];
    }

    public function render()
    {
        return '<div>spacing block</div>';
    }
}
