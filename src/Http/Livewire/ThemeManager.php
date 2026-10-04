<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Trinavo\LivewirePageBuilder\Events\DefaultThemeSet;
use Trinavo\LivewirePageBuilder\Exceptions\ThemeImportException;
use Trinavo\LivewirePageBuilder\Models\Setting;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Services\PageBuilderUIService;
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

    /** The notice that stands in for Set as Default on a locked theme. */
    public $showLockedModal = false;

    /** @var array{title: string, message: string, url: string, label: string}|null */
    #[Locked]
    public $lockedNotice = null;

    public $editingTheme = null;

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
        $defaultThemeId = Setting::getDefaultThemeId();
        $ui = $this->ui();

        $this->themes = Theme::all()
            ->sortBy(fn ($theme) => $theme->id === $defaultThemeId ? 0 : 1)
            ->values()
            ->map(fn (Theme $theme) => $theme->toArray() + [
                'preview_image_url' => $ui->getThemePreviewImageUrl($theme->previewImageUrl()),
            ])
            ->all();
    }

    public function loadDefaultTheme()
    {
        $this->defaultThemeId = Setting::getDefaultThemeId();
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
        $theme = Theme::find($themeId);

        if ($theme && $this->ui()->isThemeLocked($theme)) {
            $this->openLockedNotice($theme);

            return;
        }

        $this->themeToSetDefault = $theme;
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

        // Re-checked here: the confirm step is a separate request, and a
        // Livewire action stays callable with its button hidden.
        if ($this->ui()->isThemeLocked($this->themeToSetDefault)) {
            $theme = $this->themeToSetDefault;
            $this->closeDefaultModal();
            $this->openLockedNotice($theme);

            return;
        }

        try {
            Setting::setDefaultThemeId($this->themeToSetDefault->id);
            DefaultThemeSet::dispatch($this->themeToSetDefault->id);
            $this->defaultThemeId = $this->themeToSetDefault->id;

            $this->dispatch('notify', message: __("':name' set as default theme", ['name' => $this->themeToSetDefault->name]), type: 'success');

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

    public function closeLockedModal(): void
    {
        $this->showLockedModal = false;
        $this->lockedNotice = null;
    }

    /**
     * Say why a locked theme cannot go live, in the host's words, and where it
     * unlocks. Resolved here rather than at render, so the host's closures run
     * for the one theme pressed instead of for every theme in the list.
     */
    private function openLockedNotice(Theme $theme): void
    {
        $this->lockedNotice = $this->ui()->getLockedThemeNotice($theme);
        $this->showLockedModal = true;
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
        $this->authorizeTransfer();

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
        $this->authorizeTransfer();

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

        // Set preview theme in session; Exit Preview comes back here.
        session([
            'page_builder_preview_theme_id' => $themeId,
            PreviewBar::RETURN_URL_SESSION_KEY => route('page-builder.themes'),
        ]);

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
        session()->forget(['page_builder_preview_theme_id', PreviewBar::RETURN_URL_SESSION_KEY]);

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

            // Capture before closeCloneModal() nulls out $this->themeToClone
            $originalName = $this->themeToClone->name;

            $this->loadThemes();
            $this->closeCloneModal();

            $message = __("Theme ':name' created successfully as a copy of ':originalName'", [
                'name' => $clonedTheme->name,
                'originalName' => $originalName,
            ]);

            $this->dispatch('notify', message: $message, type: 'success');

        } catch (\Exception $e) {
            report($e);
            $this->dispatch('notify', message: __('Clone failed'), type: 'error');
        }
    }

    public function importTheme()
    {
        $this->authorizeTransfer();

        $this->validate([
            'importFile' => 'required|file|max:10240', // 10MB max, no mime restriction to support encrypted files
        ]);

        try {
            // ThemeService throws typed ThemeImportException subclasses with
            // user-friendly messages for every known bad-file case. Anything
            // else is unexpected and must bubble up so it gets logged as a bug.
            $importedTheme = $this->getThemeService()->importThemeFromFile($this->importFile->getRealPath());

            $this->loadThemes();
            $this->closeImportModal();

            $this->dispatch('notify',
                message: __("Theme ':name' imported successfully", ['name' => $importedTheme->name]),
                type: 'success'
            );
        } catch (ThemeImportException $e) {
            $this->dispatch('notify',
                message: $e->getMessage(),
                type: 'error'
            );
        } finally {
            $this->isFileUploading = false;
        }
    }

    /**
     * Export and import are refused outright when the host forbids them, not
     * just hidden: a Livewire action stays callable without its button.
     */
    private function authorizeTransfer(): void
    {
        abort_unless($this->ui()->canTransferThemes(), 403);
    }

    private function ui(): PageBuilderUIService
    {
        return app(PageBuilderUIService::class);
    }

    private function resetForm()
    {
        $this->name = '';
        $this->description = '';
    }

    public function render()
    {
        $uiService = $this->ui();

        // Get custom header HTML from UI service
        $customHeaderHtml = $uiService->getCustomThemeManagerHeaderHtml();

        // Get template gallery URL from UI service
        $templateGalleryUrl = $uiService->getTemplateGalleryUrl();

        return view('page-builder::livewire.theme-manager', [
            'canTransferThemes' => $uiService->canTransferThemes(),
            'customHeaderHtml' => $customHeaderHtml,
            'templateGalleryUrl' => $templateGalleryUrl,
        ])->layout('page-builder::layouts.app');
    }
}
