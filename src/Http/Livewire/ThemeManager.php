<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Component;
use Trinavo\LivewirePageBuilder\Models\Theme;

class ThemeManager extends Component
{
    public $themes = [];
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showDefaultModal = false;
    public $editingTheme = null;
    public $selectedTheme = null;
    public $themeToDelete = null;
    public $themeToSetDefault = null;
    
    // Form fields
    public $name = '';
    public $description = '';
    
    // Theme selection
    public $defaultThemeId = null;

    public function mount()
    {
        $this->loadThemes();
        $this->loadDefaultTheme();
    }

    public function loadThemes()
    {
        $this->themes = Theme::orderBy('name')->get()->toArray();
    }

    public function loadDefaultTheme()
    {
        // For now, we'll use session/cache to store default theme
        // In a real app, you might want to store this in settings table
        $this->defaultThemeId = session('default_theme_id');
    }

    public function selectTheme($themeId)
    {
        $this->selectedTheme = Theme::find($themeId);
        if ($this->selectedTheme) {
            session(['selected_theme_id' => $themeId]);
            $this->dispatch('theme-selected', themeId: $themeId, themeName: $this->selectedTheme->name);
            $this->dispatch('notify', message: "Theme '{$this->selectedTheme->name}' selected", type: 'success');
        }
    }

    public function confirmSetDefaultTheme($themeId)
    {
        $this->themeToSetDefault = Theme::find($themeId);
        $this->showDefaultModal = true;
    }

    public function setDefaultTheme()
    {
        if (!$this->themeToSetDefault) return;

        session(['default_theme_id' => $this->themeToSetDefault->id]);
        $this->defaultThemeId = $this->themeToSetDefault->id;
        
        $this->dispatch('notify', message: "'{$this->themeToSetDefault->name}' set as default theme", type: 'success');
        
        $this->closeDefaultModal();
    }

    public function closeDefaultModal()
    {
        $this->showDefaultModal = false;
        $this->themeToSetDefault = null;
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function openEditModal($themeId)
    {
        $theme = Theme::find($themeId);
        if ($theme) {
            $this->editingTheme = $theme;
            $this->name = $theme->name;
            $this->description = $theme->description;
            $this->showEditModal = true;
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingTheme = null;
        $this->resetForm();
    }

    public function createTheme()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:builder_themes,name',
            'description' => 'nullable|string|max:1000',
        ]);

        $theme = Theme::create([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        $this->loadThemes();
        $this->closeCreateModal();
        $this->dispatch('notify', message: "Theme '{$theme->name}' created successfully", type: 'success');
    }

    public function updateTheme()
    {
        if (!$this->editingTheme) return;

        $this->validate([
            'name' => 'required|string|max:255|unique:builder_themes,name,' . $this->editingTheme->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $this->editingTheme->update([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        $this->loadThemes();
        $this->closeEditModal();
        $this->dispatch('notify', message: "Theme '{$this->editingTheme->name}' updated successfully", type: 'success');
    }

    public function confirmDeleteTheme($themeId)
    {
        $this->themeToDelete = Theme::find($themeId);
        $this->showDeleteModal = true;
    }

    public function deleteTheme()
    {
        if (!$this->themeToDelete) return;

        // Check if theme has pages
        $pageCount = $this->themeToDelete->pages()->count();
        if ($pageCount > 0) {
            $this->dispatch('notify', 
                message: "Cannot delete theme '{$this->themeToDelete->name}' as it has {$pageCount} page(s) associated with it", 
                type: 'error'
            );
            $this->closeDeleteModal();
            return;
        }

        $themeName = $this->themeToDelete->name;
        $themeId = $this->themeToDelete->id;
        
        $this->themeToDelete->delete();
        
        // Clear default/selected if this was the one
        if ($this->defaultThemeId == $themeId) {
            session()->forget('default_theme_id');
            $this->defaultThemeId = null;
        }
        if (session('selected_theme_id') == $themeId) {
            session()->forget('selected_theme_id');
            $this->selectedTheme = null;
        }

        $this->loadThemes();
        $this->dispatch('notify', message: "Theme '{$themeName}' deleted successfully", type: 'success');
        $this->closeDeleteModal();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->themeToDelete = null;
    }

    private function resetForm()
    {
        $this->name = '';
        $this->description = '';
    }

    public function render()
    {
        // Get selected theme from session
        $selectedThemeId = session('selected_theme_id');
        $selectedTheme = $selectedThemeId ? Theme::find($selectedThemeId) : null;

        return view('page-builder::livewire.theme-manager', [
            'selectedTheme' => $selectedTheme,
        ])->layout('page-builder::layouts.app');
    }
}
