<?php

namespace Trinavo\LivewirePageBuilder\Support;

use Trinavo\LivewirePageBuilder\Models\Setting;
use Trinavo\LivewirePageBuilder\Models\Theme;

trait ThemeResolver
{
    /**
     * Resolve the theme ID to use.
     * Priority: explicit parameter -> database default -> first available theme -> null
     */
    protected function resolveThemeId($themeId = null)
    {
        // If a specific theme ID is provided, use it
        if ($themeId) {
            return $themeId;
        }

        // Fall back to database default theme
        $defaultThemeId = Setting::getDefaultThemeId();
        if ($defaultThemeId) {
            return $defaultThemeId;
        }

        // If no default, try to get the first available theme
        $firstTheme = Theme::first();
        if ($firstTheme) {
            return $firstTheme->id;
        }

        return null;
    }

    /**
     * Get the resolved theme model
     */
    protected function resolveTheme($themeId = null)
    {
        $resolvedThemeId = $this->resolveThemeId($themeId);

        return $resolvedThemeId ? Theme::find($resolvedThemeId) : null;
    }
}
