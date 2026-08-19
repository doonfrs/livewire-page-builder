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
     * Whether live edit (the gear + property sheet on the public page) is available.
     */
    private bool|Closure $liveEdit = false;

    /**
     * Optional Alpine expression the live edit gear is shown behind, so a host can
     * put the gears under its own "edit mode" switch (e.g. '$store.adminEdit?.on').
     */
    private string $liveEditToggleExpression = '';

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
     * Enable live edit on publicly rendered pages.
     *
     * Pass a closure to decide per request, e.g. from a host service provider:
     *
     *     app(PageBuilderUIService::class)
     *         ->enableLiveEdit(fn () => Auth::user()?->can('edit-pages'));
     *
     * The closure receives the page key and theme id being edited, so permission can
     * be scoped per page. Zero-argument closures keep working - PHP ignores the extra
     * positional arguments.
     *
     * @param  bool|Closure  $enabled  Static flag, or a closure resolved on every check
     */
    public function enableLiveEdit(bool|Closure $enabled = true): self
    {
        $this->liveEdit = $enabled;

        return $this;
    }

    /**
     * Whether live edit is available right now.
     *
     * Deliberately not memoised: this service is a container singleton, so caching the
     * result would leak one user's permission decision into later requests under Octane.
     */
    public function isLiveEditEnabled(?string $pageKey = null, $themeId = null): bool
    {
        if ($this->liveEdit instanceof Closure) {
            return (bool) ($this->liveEdit)($pageKey, $themeId);
        }

        return $this->liveEdit;
    }

    /**
     * Set an Alpine expression the live edit gear is shown behind.
     *
     * @param  string  $expression  e.g. '$store.adminEdit?.on'. Empty means always visible.
     */
    public function setLiveEditToggleExpression(string $expression = ''): self
    {
        $this->liveEditToggleExpression = $expression;

        return $this;
    }

    /**
     * Get the Alpine expression gating the live edit gear, if any.
     */
    public function getLiveEditToggleExpression(): string
    {
        return $this->liveEditToggleExpression;
    }

    /**
     * Clear all custom UI settings
     */
    public function clear(): self
    {
        $this->customHeaderHtml = '';
        $this->customThemeManagerHeaderHtml = '';
        $this->templateGalleryUrl = '';
        $this->liveEdit = false;
        $this->liveEditToggleExpression = '';

        return $this;
    }
}
