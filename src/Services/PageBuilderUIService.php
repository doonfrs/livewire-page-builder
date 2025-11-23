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
     * Template gallery URL to be used in theme manager
     */
    private string|Closure $templateGalleryUrl = '';

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
     * Set the template gallery URL
     *
     * @param  string|Closure  $url  The URL to the template gallery (or a closure that returns the URL)
     */
    public function setTemplateGalleryUrl(string|Closure $url): self
    {
        $this->templateGalleryUrl = $url;

        return $this;
    }

    /**
     * Get the template gallery URL
     *
     * @return string The template gallery URL
     */
    public function getTemplateGalleryUrl(): string
    {
        if ($this->templateGalleryUrl instanceof Closure) {
            return ($this->templateGalleryUrl)();
        }

        return $this->templateGalleryUrl;
    }

    /**
     * Clear all custom UI settings
     */
    public function clear(): self
    {
        $this->customHeaderHtml = '';
        $this->customThemeManagerHeaderHtml = '';
        $this->templateGalleryUrl = '';

        return $this;
    }
}
