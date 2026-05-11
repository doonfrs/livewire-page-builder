<?php

namespace Trinavo\LivewirePageBuilder\Exceptions;

use Exception;

/**
 * Base exception for expected, user-input theme-import failures.
 *
 * The message is already user-facing and translated; the catcher can surface
 * it directly without calling report(). Unknown failures must NOT be wrapped
 * in this type — let them bubble so they're logged as real bugs.
 */
class ThemeImportException extends Exception
{
}
