<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

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
            report($e);

            $this->dispatch('notify', message: __('Error creating theme'), type: 'error');
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
            report($e);
            $this->dispatch('notify', message: __('Error updating theme'), type: 'error');
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
            report($e);
            $this->dispatch('notify', message: __('Error setting default theme'), type: 'error');
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

        // Check if this is the default theme
        if ($this->themeToDelete->id == $this->defaultThemeId) {
            $this->dispatch('notify', message: __('Cannot delete the default theme'), type: 'error');
            $this->closeDeleteModal();

            return;
        }

        try {
            // Delete all pages associated with this theme
            $this->themeToDelete->pages()->delete();

            // Delete the theme
            $this->themeToDelete->delete();

            $this->loadThemes();
            $this->dispatch('notify', message: __('Theme deleted successfully'), type: 'success');

            // Close the delete modal after successful deletion
            $this->closeDeleteModal();

        } catch (\Exception $e) {
            report($e);
            $this->dispatch('notify', message: __('Error deleting theme'), type: 'error');
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
        $theme = Theme::find($themeId);

        if (! $theme) {
            $this->dispatch('notify', message: __('Theme not found'), type: 'error');

            return;
        }

        // Check if encryption is enabled and use appropriate export method
        if ($themeService->isEncryptionEnabled()) {
            // Export with encryption (transparent to user)
            $encryptedData = $themeService->exportThemeAsEncryptedJson($themeId);

            if (! $encryptedData) {
                $this->dispatch('notify', message: __('Theme not found'), type: 'error');

                return;
            }

            $extension = $themeService->getEncryptionService()->getFileExtension();
            $fileName = 'theme-'.Str::slug($theme->name).'-'.now()->format('Y-m-d-H-i-s').$extension;

            // Show success notification
            $this->dispatch('notify',
                message: __('Theme \':name\' exported successfully', ['name' => $theme->name]),
                type: 'success'
            );

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

            $fileName = 'theme-'.Str::slug($theme->name).'-'.now()->format('Y-m-d-H-i-s').'.json';

            // Show success notification
            $this->dispatch('notify',
                message: __('Theme \':name\' exported successfully', ['name' => $theme->name]),
                type: 'success'
            );

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
        $this->resetForm();
        $this->resetValidation();
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
        $this->cloneName = __('Copy of').' '.$this->themeToClone->name;
        $this->showCloneModal = true;
    }

    public function closeCloneModal()
    {
        $this->showCloneModal = false;
        $this->themeToClone = null;
        $this->cloneName = '';
    }

    /**
     * Set a theme as preview and redirect to homepage
     */
    public function previewTheme($themeId)
    {
        $theme = Theme::find($themeId);

        if (! $theme) {
            $this->dispatch('notify', message: __('Theme not found'), type: 'error');

            return;
        }

        // Set preview theme in session
        session(['page_builder_preview_theme_id' => $themeId]);

        $this->dispatch('notify',
            message: __("Previewing theme ':name'", ['name' => $theme->name]),
            type: 'info'
        );

        // Redirect to app homepage
        return redirect('/');
    }

    /**
     * Cancel preview mode and return to normal theme
     */
    public function cancelPreview()
    {
        session()->forget('page_builder_preview_theme_id');

        $this->dispatch('notify', message: __('Preview mode cancelled'), type: 'success');

        return redirect('/page-builder/themes');
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
            report($e);
            $this->dispatch('notify', message: __('Clone failed'), type: 'error');
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
                // Check if the file content could be read
                $content = file_get_contents($this->importFile->getRealPath());

                if ($content === false) {
                    throw new \Exception(__('Unable to read the selected file. Please ensure the file is not corrupted and try again.'));
                }

                // Check if it's an encrypted file
                if ($this->getThemeService()->getEncryptionService()->isEncrypted($content)) {
                    throw new \Exception(__('This file appears to be encrypted but cannot be decrypted. Please ensure you have the correct encryption key configured.'));
                }

                // Check if it's valid JSON
                $decoded = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception(__('The selected file does not contain valid JSON data. Please select a valid theme file.'));
                }

                // Check if it has the required structure
                if (! isset($decoded['name']) || ! isset($decoded['pages'])) {
                    throw new \Exception(__('The selected file does not appear to be a valid theme file. It is missing required fields (name or pages).'));
                }

                throw new \Exception(__('Import failed - the file does not appear to be a valid theme file'));
            }

            $this->loadThemes();
            $this->closeImportModal();

            $this->dispatch('notify',
                message: __("Theme ':name' imported successfully", ['name' => $importedTheme->name]),
                type: 'success'
            );

        } catch (\Exception $e) {
            report($e);

            // Provide user-friendly error messages
            $errorMessage = $e->getMessage();

            // Check for specific encryption-related errors (handle both prefixed and direct messages)
            if (str_contains($errorMessage, 'No encryption key provided')) {
                $errorMessage = __('This file is encrypted but no encryption key is configured. Please contact your administrator to set up the encryption key.');
            } elseif (str_contains($errorMessage, 'Failed to decrypt')) {
                $errorMessage = __('This file is encrypted but cannot be decrypted. The encryption key may be incorrect or the file may be corrupted.');
            } elseif (str_contains($errorMessage, 'Invalid encrypted theme format')) {
                $errorMessage = __('This file appears to be encrypted but has an invalid format. It may be corrupted or created with a different version.');
            } elseif (str_contains($errorMessage, 'Invalid JSON format')) {
                $errorMessage = __('The selected file does not contain valid JSON data. Please select a valid theme file.');
            } elseif (str_contains($errorMessage, 'File not found')) {
                $errorMessage = __('This file could not be found. Please ensure the file exists and try again.');
            } elseif (str_contains($errorMessage, 'Failed to read file')) {
                $errorMessage = __('Unable to read the selected file. Please ensure the file is not corrupted and try again.');
            } elseif (str_contains($errorMessage, 'missing required fields')) {
                $errorMessage = __('The selected file does not appear to be a valid theme file. It is missing required fields (name or pages).');
            } elseif (str_contains($errorMessage, 'Invalid pages format')) {
                $errorMessage = __('The selected file contains invalid page data. Please ensure it is a properly formatted theme file.');
            } elseif (str_contains($errorMessage, 'missing key')) {
                $errorMessage = __('The selected file contains invalid page data. Some pages are missing required information.');
            } elseif (str_contains($errorMessage, 'Failed to import encrypted theme')) {
                // Handle the prefixed error message
                if (str_contains($errorMessage, 'No encryption key provided')) {
                    $errorMessage = __('This file is encrypted but no encryption key is configured. Please contact your administrator to set up the encryption key.');
                } elseif (str_contains($errorMessage, 'Failed to decrypt')) {
                    $errorMessage = __('This file is encrypted but cannot be decrypted. The encryption key may be incorrect or the file may be corrupted.');
                } elseif (str_contains($errorMessage, 'Invalid encrypted theme format')) {
                    $errorMessage = __('This file appears to be encrypted but has an invalid format. It may be corrupted or created with a different version.');
                } else {
                    $errorMessage = __('This encrypted file could not be imported. Please check your encryption configuration.');
                }
            } elseif (str_contains($errorMessage, 'Failed to import theme data')) {
                // Handle the prefixed error message for regular theme data
                if (str_contains($errorMessage, 'Invalid theme file format')) {
                    $errorMessage = __('The selected file does not appear to be a valid theme file. It is missing required fields (name or pages).');
                } elseif (str_contains($errorMessage, 'Invalid pages format')) {
                    $errorMessage = __('The selected file contains invalid page data. Please ensure it is a properly formatted theme file.');
                } elseif (str_contains($errorMessage, 'missing key')) {
                    $errorMessage = __('The selected file contains invalid page data. Some pages are missing required information.');
                } else {
                    $errorMessage = __('The theme file could not be imported due to data format issues.');
                }
            }

            $this->dispatch('notify',
                message: $errorMessage,
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
        // Get custom header HTML from UI service
        $customHeaderHtml = app(\Trinavo\LivewirePageBuilder\Services\PageBuilderUIService::class)->getCustomThemeManagerHeaderHtml();

        return view('page-builder::livewire.theme-manager', [
            'selectedTheme' => $this->selectedTheme,
            'customHeaderHtml' => $customHeaderHtml,
        ])->layout('page-builder::layouts.app');
    }
}
