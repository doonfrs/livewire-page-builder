<?php

namespace Trinavo\LivewirePageBuilder\Config;

class Variables
{
    /**
     * The registered variables.
     *
     * @var array
     */
    protected static $variables = [];

    /**
     * Register a new variable.
     *
     * @param  mixed  $value  String or callable
     */
    public static function register(string $name, $value): void
    {
        static::$variables[$name] = $value;
    }

    /**
     * Register multiple variables at once.
     */
    public static function registerMany(array $variables): void
    {
        foreach ($variables as $name => $value) {
            static::register($name, $value);
        }
    }

    /**
     * Get a variable value.
     *
     * @param  mixed  $default
     * @return mixed
     */
    public static function get(string $name, $default = null)
    {
        if (! isset(static::$variables[$name])) {
            return $default;
        }

        $value = static::$variables[$name];

        if (is_callable($value)) {
            return call_user_func($value);
        }

        return $value;
    }

    /**
     * Get all variables.
     */
    public static function all(): array
    {
        $resolvedVariables = [];

        foreach (static::$variables as $name => $value) {
            $resolvedVariables[$name] = is_callable($value) ? call_user_func($value) : $value;
        }

        return $resolvedVariables;
    }

    /**
     * Check if a variable exists.
     */
    public static function has(string $name): bool
    {
        return isset(static::$variables[$name]);
    }

    /**
     * Remove a variable.
     */
    public static function remove(string $name): void
    {
        unset(static::$variables[$name]);
    }

    /**
     * Clear all variables.
     */
    public static function clear(): void
    {
        static::$variables = [];
    }
}
