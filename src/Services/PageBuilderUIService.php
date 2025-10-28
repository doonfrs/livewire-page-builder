<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Closure;

class PageBuilderUIService
{
    /**
     * Custom HTML to be rendered in the page editor header
     */
    private string|Closure $customHeaderHtml = '';

    /**
     * Custom HTML to be rendered in the theme manager header
     */
    private string|Closure $customThemeManagerHeaderHtml = '';

    /**
     * Set custom HTML to be rendered in the page editor header
     *
     * @param  string|Closure  $html  The HTML to render in the header (or a closure that returns HTML)
     */
    public function setCustomHeaderHtml(string|Closure $html): self
    {
        $this->customHeaderHtml = $html;

        return $this;
    }

    /**
     * Get custom HTML for the page editor header
     *
     * @return string The custom HTML
     */
    public function getCustomHeaderHtml(): string
    {
        if ($this->customHeaderHtml instanceof Closure) {
            return ($this->customHeaderHtml)();
        }

        return $this->customHeaderHtml;
    }

    /**
     * Set custom HTML to be rendered in the theme manager header
     *
     * @param  string|Closure  $html  The HTML to render in the header (or a closure that returns HTML)
     */
    public function setCustomThemeManagerHeaderHtml(string|Closure $html): self
    {
        $this->customThemeManagerHeaderHtml = $html;

        return $this;
    }

    /**
     * Get custom HTML for the theme manager header
     *
     * @return string The custom HTML
     */
    public function getCustomThemeManagerHeaderHtml(): string
    {
        if ($this->customThemeManagerHeaderHtml instanceof Closure) {
            return ($this->customThemeManagerHeaderHtml)();
        }

        return $this->customThemeManagerHeaderHtml;
    }

    /**
     * Clear all custom UI settings
     */
    public function clear(): self
    {
        $this->customHeaderHtml = '';
        $this->customThemeManagerHeaderHtml = '';

        return $this;
    }
}
