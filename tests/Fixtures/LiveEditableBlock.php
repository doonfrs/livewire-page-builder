<?php

namespace Trinavo\LivewirePageBuilder\Tests\Fixtures;

use Trinavo\LivewirePageBuilder\Support\Block;
use Trinavo\LivewirePageBuilder\Support\Properties\TextProperty;

/**
 * A block that opts into live edit, declaring a mix of object and string entries.
 */
class LiveEditableBlock extends Block
{
    public $title = 'Hello';

    public $subtitle = 'World';

    public $internalNote = 'not editable live';

    public function getPageBuilderProperties(): array
    {
        return [
            new TextProperty('title', 'Title', defaultValue: $this->title),
            new TextProperty('subtitle', 'Subtitle', defaultValue: $this->subtitle),
            new TextProperty('internalNote', 'Internal note', defaultValue: $this->internalNote),
        ];
    }

    public function getPageBuilderLiveEditProperties(): array
    {
        return [
            // Object form, plus a name resolved against getAllProperties(), plus a name
            // that does not exist and must be dropped rather than blow up.
            new TextProperty('title', 'Title', defaultValue: $this->title),
            'subtitle',
            'noSuchProperty',
        ];
    }

    public function render()
    {
        return '<div>live editable block</div>';
    }
}
