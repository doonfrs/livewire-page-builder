<?php

namespace Trinavo\LivewirePageBuilder\Facades;

use Illuminate\Support\Facades\Facade;
use Trinavo\LivewirePageBuilder\Config\Variables;

/**
 * @method static void register(string $name, mixed $value)
 * @method static void registerMany(array $variables)
 * @method static mixed get(string $name, mixed $default = null)
 * @method static array all()
 * @method static bool has(string $name)
 * @method static void remove(string $name)
 * @method static void clear()
 *
 * @see \Trinavo\LivewirePageBuilder\Config\Variables
 */
class PageBuilderVariables extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Variables::class;
    }
}
