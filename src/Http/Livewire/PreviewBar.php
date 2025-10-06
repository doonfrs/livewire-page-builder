<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Component;
use Trinavo\LivewirePageBuilder\Models\Theme;

class PreviewBar extends Component
{
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

        return redirect('/');
    }

    public function render()
    {
        return view('page-builder::livewire.preview-bar');
    }
}
