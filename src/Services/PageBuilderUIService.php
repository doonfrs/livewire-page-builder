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
     * The daisyUI theme name the host site renders with, painted on the editor's
     * preview surfaces so a block looks in the editor the way it looks live.
     */
    private string|Closure $previewThemeName = '';

    /**
     * The host's own theme declarations (--color-*, --radius-*, ...) for a theme
     * daisyUI does not ship, injected on the same surfaces.
     */
    private string|Closure $previewThemeCss = '';

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
     * Give the editor the host site's theme, so the preview canvas and the colour
     * swatches render with the real palette instead of daisyUI's stock one.
     *
     * Both halves matter, because a theme can be either shape: a built-in daisyUI
     * theme only needs its name on the element, while a theme the host defined
     * itself needs its declarations too - the name alone matches no rule.
     *
     *     app(PageBuilderUIService::class)->setPreviewTheme(
     *         name: fn () => app_color_theme(),
     *         css: fn () => app_custom_theme_styles(),
     *     );
     *
     * Pass closures so the answer is re-resolved per request; a multi-tenant host
     * has a different theme on every domain.
     *
     * @param  string|Closure  $name  daisyUI theme name for the data-theme attribute
     * @param  string|Closure  $css  Bare CSS declarations, no selector and no braces
     */
    public function setPreviewTheme(string|Closure $name = '', string|Closure $css = ''): self
    {
        $this->previewThemeName = $name;
        $this->previewThemeCss = $css;

        return $this;
    }

    /**
     * The theme name for the preview surfaces, or '' when the host set none.
     *
     * Not memoised, for the same reason as isLiveEditEnabled(): the service is a
     * container singleton, so one tenant's theme would outlive its request.
     */
    public function getPreviewThemeName(): string
    {
        if ($this->previewThemeName instanceof Closure) {
            return (string) ($this->previewThemeName)();
        }

        return $this->previewThemeName;
    }

    /**
     * The attributes that turn an element into a preview surface, ready to echo.
     *
     * Built here rather than with an @if in each view: three views mark a surface, and a
     * conditional attribute in Blade leaves a newline and an indent in the middle of the
     * tag, which is ugly to read and awkward to assert on.
     */
    public function getPreviewThemeAttributes(): string
    {
        $name = $this->getPreviewThemeName();

        return $name === ''
            ? 'data-pb-theme'
            : 'data-pb-theme data-theme="'.e($name).'"';
    }

    /**
     * The theme declarations for the preview surfaces, or '' when the host set none.
     *
     * `<` is stripped because this is echoed unescaped inside a <style> element, where
     * a `</style>` in the value would end the stylesheet and hand the rest to the HTML
     * parser. No valid CSS declaration contains one.
     */
    public function getPreviewThemeCss(): string
    {
        $css = $this->previewThemeCss instanceof Closure
            ? (string) ($this->previewThemeCss)()
            : $this->previewThemeCss;

        return str_replace('<', '', $css);
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
        $this->previewThemeName = '';
        $this->previewThemeCss = '';

        return $this;
    }
}
