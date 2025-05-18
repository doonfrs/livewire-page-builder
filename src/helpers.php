<?php

use Trinavo\LivewirePageBuilder\Support\VariablesParser;

if (! function_exists('pb_parse_variables')) {
    /**
     * Parse variables in a text string.
     */
    function pb_parse_variables(string $text): string
    {
        return VariablesParser::parse($text);
    }
}
