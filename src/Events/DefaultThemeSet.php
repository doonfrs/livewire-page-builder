<?php

namespace Trinavo\LivewirePageBuilder\Events;

use Illuminate\Foundation\Events\Dispatchable;

class DefaultThemeSet
{
    use Dispatchable;

    public function __construct(public readonly int $themeId) {}
}
