<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Trinavo\LivewirePageBuilder\Services\LocalizationService;

class LanguageSwitcher extends Component
{
    /**
     * Current UI locale
     */
    public string $currentLocale;

    /**
     * Available UI locales
     */
    public array $availableLocales = [];

    /**
     * Mount the component
     */
    public function mount()
    {
        $localizationService = $this->getLocalizationService();
        $this->availableLocales = $localizationService->getUiLocales();

        // First check session, then fall back to app locale
        $this->currentLocale = session('page_builder_locale', app()->getLocale());
    }

    /**
     * Get the localization service
     */
    protected function getLocalizationService(): LocalizationService
    {
        return app(LocalizationService::class);
    }

    /**
     * Switch to a different locale
     */
    public function switchLocale(string $locale)
    {
        // Validate locale is available
        if (!array_key_exists($locale, $this->availableLocales)) {
            return;
        }

        // Store in session using various methods to ensure it persists
        Session::put('page_builder_locale', $locale);
        Session::save();

        // Also set a cookie as a backup method
        //  cookie()->queue('page_builder_locale', $locale, 60 * 24 * 30); // 30 days

        // Set the locale immediately for the current request
        app()->setLocale($locale);
        $this->currentLocale = $locale;

        // Use both dispatch methods for Livewire 3 compatibility
        $this->dispatch('refreshPage');

        return redirect(request()->header('Referer'));
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('page-builder::livewire.language-switcher');
    }
}
