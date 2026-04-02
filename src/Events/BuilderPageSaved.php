<?php

namespace Trinavo\LivewirePageBuilder\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;

class BuilderPageSaved
{
    use Dispatchable;

    public function __construct(public readonly BuilderPage $page) {}
}
