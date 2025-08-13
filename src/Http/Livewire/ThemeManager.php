<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Trinavo\LivewirePageBuilder\Models\Setting;
use Trinavo\LivewirePageBuilder\Models\Theme;

class ThemeManager extends Component
{
    use WithFileUploads;

    public $themes = [];

    public $showCreateModal = false;

    public $showEditModal = false;

    public $showDeleteModal = false;

    public $showDefaultModal = false;

    public $showImportModal = false;

    public $editingTheme = null;

    public $selectedTheme = null;

    public $themeToDelete = null;

    public $themeToSetDefault = null;

    // Form fields
    public $name = '';

    public $description = '';

    // Theme selection
    public $defaultThemeId = null;

    // Import
    public $importFile = null;

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
        $this->defaultThemeId = Setting::getDefaultThemeId();
    }

    public function selectTheme($themeId)
    {
        $this->selectedTheme = Theme::find($themeId);
        if ($this->selectedTheme) {
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
        if (! $this->themeToSetDefault) {
            return;
        }

        Setting::setDefaultThemeId($this->themeToSetDefault->id);
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
        if (! $this->editingTheme) {
            return;
        }

        $this->validate([
            'name' => 'required|string|max:255|unique:builder_themes,name,'.$this->editingTheme->id,
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
        if (! $this->themeToDelete) {
            $this->dispatch('notify', message: 'Theme not found', type: 'error');

            return;
        }
        $this->showDeleteModal = true;
    }

    public function deleteTheme()
    {
        if (! $this->themeToDelete) {
            $this->dispatch('notify', message: 'No theme selected for deletion', type: 'error');

            return;
        }

        try {
            $themeName = $this->themeToDelete->name;
            $themeId = $this->themeToDelete->id;

            // Check if theme has pages and inform user they will be orphaned
            $pageCount = $this->themeToDelete->pages()->count();

            // Delete the theme (foreign key constraint will set theme_id to null in pages)
            $this->themeToDelete->delete();

            // Clear default if this was the one
            if ($this->defaultThemeId == $themeId) {
                Setting::setDefaultThemeId(null);
                $this->defaultThemeId = null;
            }

            $this->loadThemes();

            if ($pageCount > 0) {
                $this->dispatch('notify',
                    message: "Theme '{$themeName}' deleted successfully. {$pageCount} page(s) are now unassigned to any theme.",
                    type: 'success'
                );
            } else {
                $this->dispatch('notify', message: "Theme '{$themeName}' deleted successfully", type: 'success');
            }

            $this->closeDeleteModal();

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Error deleting theme: '.$e->getMessage(), type: 'error');
            $this->closeDeleteModal();
        }
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->themeToDelete = null;
    }

    public function exportTheme($themeId)
    {
        $theme = Theme::with('pages')->find($themeId);

        if (! $theme) {
            $this->dispatch('notify', message: 'Theme not found', type: 'error');

            return;
        }

        $exportData = [
            'name' => $theme->name,
            'description' => $theme->description,
            'pages' => $theme->pages->map(function ($page) {
                return [
                    'key' => $page->key,
                    'name' => $page->name,
                    'is_active' => $page->is_active,
                    'content' => $page->content,
                    'created_at' => $page->created_at,
                ];
            })->toArray(),
            'exported_at' => now()->toISOString(),
            'version' => '1.0',
        ];

        $fileName = 'theme-'.Str::slug($theme->name).'-'.now()->format('Y-m-d-H-i-s').'.json';

        return response()->streamDownload(function () use ($exportData) {
            echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $fileName, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function openImportModal()
    {
        $this->showImportModal = true;
        $this->importFile = null;
    }

    public function closeImportModal()
    {
        $this->showImportModal = false;
        $this->importFile = null;
    }

    public function importTheme()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:json|max:10240', // 10MB max
        ]);

        try {
            $content = file_get_contents($this->importFile->getRealPath());
            $data = json_decode($content, true);

            if (! $data) {
                throw new \Exception('Invalid JSON format');
            }

            // Validate required fields
            if (! isset($data['name']) || ! isset($data['pages'])) {
                throw new \Exception('Invalid theme file format');
            }

            // Check if theme name already exists
            $existingTheme = Theme::where('name', $data['name'])->first();
            if ($existingTheme) {
                // Generate unique name
                $originalName = $data['name'];
                $counter = 1;
                do {
                    $data['name'] = $originalName.' ('.$counter.')';
                    $counter++;
                } while (Theme::where('name', $data['name'])->exists());
            }

            // Create theme
            $theme = Theme::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
            ]);

            // Import pages
            foreach ($data['pages'] as $pageData) {
                $theme->pages()->create([
                    'key' => $pageData['key'],
                    'name' => $pageData['name'],
                    'is_active' => $pageData['is_active'] ?? true,
                    'content' => $pageData['content'] ?? [],
                ]);
            }

            $this->loadThemes();
            $this->closeImportModal();

            $this->dispatch('notify',
                message: "Theme '{$theme->name}' imported successfully with ".count($data['pages']).' pages',
                type: 'success'
            );

        } catch (\Exception $e) {
            $this->dispatch('notify',
                message: 'Import failed: '.$e->getMessage(),
                type: 'error'
            );
        }
    }

    private function resetForm()
    {
        $this->name = '';
        $this->description = '';
    }

    public function render()
    {
        return view('page-builder::livewire.theme-manager', [
            'selectedTheme' => $this->selectedTheme,
        ])->layout('page-builder::layouts.app');
    }
}
