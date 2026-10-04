<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Component;
use Trinavo\LivewirePageBuilder\Models\Theme;

class PreviewBar extends Component
{
    /**
     * Where Exit Preview goes: the screen the preview was started from, set
     * by whoever starts it. The home page when nothing set it.
     */
    public const RETURN_URL_SESSION_KEY = 'page_builder_preview_return_url';

    public $previewThemeId;

    public $themes = [];

    public function mount()
    {
        $this->previewThemeId = session('page_builder_preview_theme_id');
        $this->loadThemes();
    }

    public function loadThemes()
    {
        $this->themes = Theme::orderBy('name')->get()->toArray();
    }

    public function updatedPreviewThemeId($value)
    {
        if (! $value) {
            return;
        }

        $theme = Theme::find($value);

        if (! $theme) {
            return;
        }

        // Update preview session
        session(['page_builder_preview_theme_id' => $value]);

        // Use JavaScript to refresh the page
        $this->dispatch('refresh-page');
    }

    public function cancelPreview()
    {
        session()->forget('page_builder_preview_theme_id');

        return redirect(session()->pull(self::RETURN_URL_SESSION_KEY, '/'));
    }

    public function render()
    {
        return view('page-builder::livewire.preview-bar');
    }
}
