<?php

namespace Trinavo\LivewirePageBuilder\Support;

use Trinavo\LivewirePageBuilder\Config\Variables;

class VariablesParser
{
    /**
     * Parse variables in a text string.
     * Replaces {variable_name} with the corresponding variable value.
     */
    public static function parse(string $text): string
    {
        return preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function ($matches) {
            $variableName = $matches[1];

            if (Variables::has($variableName)) {
                $value = Variables::get($variableName);

                return is_string($value) || is_numeric($value) ? $value : '';
            }

            return $matches[0]; // Return the original placeholder if variable not found
        }, $text);
    }

    /**
     * Check if a text contains any variable placeholders.
     */
    public static function containsVariables(string $text): bool
    {
        return preg_match('/\{([a-zA-Z0-9_]+)\}/', $text) === 1;
    }

    /**
     * List all variables used in a text.
     */
    public static function listVariablesInText(string $text): array
    {
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $text, $matches);

        return $matches[1] ?? [];
    }
}
