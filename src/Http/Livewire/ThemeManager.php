<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Trinavo\LivewirePageBuilder\Models\Setting;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Services\ThemeService;

class ThemeManager extends Component
{
    use WithFileUploads;

    public $themes = [];

    public $showCreateModal = false;

    public $showEditModal = false;

    public $showDeleteModal = false;

    public $showDefaultModal = false;

    public $showImportModal = false;

    public $showCloneModal = false;

    public $editingTheme = null;

    public $selectedTheme = null;

    public $themeToDelete = null;

    public $themeToSetDefault = null;

    public $themeToClone = null;

    // Form fields
    public $name = '';

    public $description = '';

    // Clone fields
    public $cloneName = '';

    // Theme selection
    public $defaultThemeId = null;

    // Import
    public $importFile = null;

    public $isFileUploading = false;

    public function mount()
    {
        $this->loadThemes();
        $this->loadDefaultTheme();
    }

    /**
     * Get the ThemeService instance from the container
     */
    private function getThemeService(): ThemeService
    {
        return app(ThemeService::class);
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
    }

    public function openCreateModal()
    {
        $this->showCreateModal = true;
        $this->resetForm();
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function openEditModal($themeId)
    {
        $this->editingTheme = Theme::find($themeId);
        if ($this->editingTheme) {
            $this->name = $this->editingTheme->name;
            $this->description = $this->editingTheme->description;
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
            'description' => 'nullable|string',
        ]);

        try {
            Theme::create([
                'name' => $this->name,
                'description' => $this->description,
            ]);

            $this->loadThemes();
            $this->closeCreateModal();

            $this->dispatch('notify', message: __('Theme created successfully'), type: 'success');

        } catch (\Exception $e) {
            Log::error('Failed to create theme', [
                'name' => $this->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->dispatch('notify', message: __('Error creating theme: :message', ['message' => $e->getMessage()]), type: 'error');
        }
    }

    public function updateTheme()
    {
        if (! $this->editingTheme) {
            return;
        }

        $this->validate([
            'name' => 'required|string|max:255|unique:builder_themes,name,'.$this->editingTheme->id,
            'description' => 'nullable|string',
        ]);

        try {
            $this->editingTheme->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);

            $this->loadThemes();
            $this->closeEditModal();

            $this->dispatch('notify', message: __('Theme updated successfully'), type: 'success');

        } catch (\Exception $e) {
            Log::error('Failed to update theme', [
                'theme_id' => $this->editingTheme->id,
                'name' => $this->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->dispatch('notify', message: __('Error updating theme: :message', ['message' => $e->getMessage()]), type: 'error');
        }
    }

    public function openDeleteModal($themeId)
    {
        $this->themeToDelete = Theme::find($themeId);
        $this->showDeleteModal = true;
    }

    /**
     * Alias for openDeleteModal to maintain backward compatibility
     */
    public function confirmDeleteTheme($themeId)
    {
        $this->openDeleteModal($themeId);
    }

    /**
     * Open modal to confirm setting default theme
     */
    public function confirmSetDefaultTheme($themeId)
    {
        $this->themeToSetDefault = Theme::find($themeId);
        $this->showDefaultModal = true;
    }

    /**
     * Set the selected theme as default
     */
    public function setDefaultTheme()
    {
        if (! $this->themeToSetDefault) {
            return;
        }

        try {
            Setting::setDefaultThemeId($this->themeToSetDefault->id);
            $this->defaultThemeId = $this->themeToSetDefault->id;

            $this->dispatch('notify', message: "'{$this->themeToSetDefault->name}' set as default theme", type: 'success');

            $this->closeDefaultModal();

        } catch (\Exception $e) {
            Log::error('Failed to set default theme', [
                'theme_id' => $this->themeToSetDefault->id,
                'theme_name' => $this->themeToSetDefault->name,
                'error' => $e->getMessage(),
            ]);

            $this->dispatch('notify', message: __('Error setting default theme: :message', ['message' => $e->getMessage()]), type: 'error');
        }
    }

    /**
     * Close the default theme modal
     */
    public function closeDefaultModal()
    {
        $this->showDefaultModal = false;
        $this->themeToSetDefault = null;
    }

    public function deleteTheme()
    {
        if (! $this->themeToDelete) {
            return;
        }

        try {
            // Check if this is the default theme
            if ($this->themeToDelete->id == $this->defaultThemeId) {
                throw new \Exception(__('Cannot delete the default theme'));
            }

            // Delete all pages associated with this theme
            $this->themeToDelete->pages()->delete();

            // Delete the theme
            $this->themeToDelete->delete();

            $this->loadThemes();
            $this->dispatch('notify', message: __('Theme deleted successfully'), type: 'success');

            // Close the delete modal after successful deletion
            $this->closeDeleteModal();

        } catch (\Exception $e) {
            Log::error('Failed to delete theme', [
                'theme_id' => $this->themeToDelete?->id,
                'theme_name' => $this->themeToDelete?->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->dispatch('notify', message: __('Error deleting theme: :message', ['message' => $e->getMessage()]), type: 'error');
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
        $themeService = $this->getThemeService();

        // Check if encryption is enabled and use appropriate export method
        if ($themeService->isEncryptionEnabled()) {
            // Export with encryption (transparent to user)
            $encryptedData = $themeService->exportThemeAsEncryptedJson($themeId);

            if (! $encryptedData) {
                $this->dispatch('notify', message: __('Theme not found'), type: 'error');

                return;
            }

            $theme = Theme::find($themeId);
            $extension = $themeService->getEncryptionService()->getFileExtension();
            $fileName = 'theme-'.Str::slug($theme->name).'-'.now()->format('Y-m-d-H-i-s').$extension;

            return response()->streamDownload(function () use ($encryptedData) {
                echo $encryptedData;
            }, $fileName, [
                'Content-Type' => 'application/json',
            ]);
        } else {
            // Export without encryption (original behavior)
            $exportData = $themeService->exportTheme($themeId);

            if (! $exportData) {
                $this->dispatch('notify', message: __('Theme not found'), type: 'error');

                return;
            }

            $theme = Theme::find($themeId);
            $fileName = 'theme-'.Str::slug($theme->name).'-'.now()->format('Y-m-d-H-i-s').'.json';

            return response()->streamDownload(function () use ($exportData) {
                echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }, $fileName, [
                'Content-Type' => 'application/json',
            ]);
        }
    }

    public function openImportModal()
    {
        $this->showImportModal = true;
        $this->importFile = null;
        $this->isFileUploading = false;
    }

    public function closeImportModal()
    {
        $this->showImportModal = false;
        $this->importFile = null;
        $this->isFileUploading = false;
    }

    public function updatedImportFile()
    {
        // Simple check - if file is selected, enable the button
        $this->isFileUploading = ! empty($this->importFile);
    }

    public function openCloneModal($themeId)
    {
        $this->themeToClone = Theme::find($themeId);
        if (! $this->themeToClone) {
            $this->dispatch('notify', message: __('Theme not found'), type: 'error');

            return;
        }

        // Pre-fill clone name with "Copy of {original name}"
        $this->cloneName = 'Copy of '.$this->themeToClone->name;
        $this->showCloneModal = true;
    }

    public function closeCloneModal()
    {
        $this->showCloneModal = false;
        $this->themeToClone = null;
        $this->cloneName = '';
    }

    public function cloneTheme()
    {
        if (! $this->themeToClone) {
            $this->dispatch('notify', message: __('No theme selected for cloning'), type: 'error');

            return;
        }

        $this->validate([
            'cloneName' => 'required|string|max:255|unique:builder_themes,name',
        ]);

        try {
            $clonedTheme = $this->getThemeService()->cloneTheme($this->themeToClone, $this->cloneName);

            if (! $clonedTheme) {
                throw new \Exception(__('Cloning failed'));
            }

            $this->loadThemes();
            $this->closeCloneModal();

            $message = __("Theme ':name' created successfully as a copy of ':originalName'", [
                'name' => $clonedTheme->name,
                'originalName' => $this->themeToClone->name,
            ]);

            $this->dispatch('notify', message: $message, type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('notify', message: __('Clone failed: :message', ['message' => $e->getMessage()]), type: 'error');
        }
    }

    public function importTheme()
    {
        $this->validate([
            'importFile' => 'required|file|max:10240', // 10MB max, no mime restriction to support encrypted files
        ]);

        try {
            // The ThemeService will automatically detect if the file is encrypted
            // and handle it appropriately - transparent to the user
            $importedTheme = $this->getThemeService()->importThemeFromFile($this->importFile->getRealPath());

            if (! $importedTheme) {
                throw new \Exception(__('Import failed - the file does not appear to be a valid theme file'));
            }

            $this->loadThemes();
            $this->closeImportModal();

            $this->dispatch('notify',
                message: __("Theme ':name' imported successfully", ['name' => $importedTheme->name]),
                type: 'success'
            );

        } catch (\Exception $e) {
            $this->dispatch('notify',
                message: __('Import failed: :message', ['message' => $e->getMessage()]),
                type: 'error'
            );
        } finally {
            $this->isFileUploading = false;
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
