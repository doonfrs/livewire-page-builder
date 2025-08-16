<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        // Store the theme name before updating, so we can use it in the success message
        $themeName = $this->editingTheme->name;

        $this->editingTheme->update([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        $this->loadThemes();
        $this->closeEditModal();
        $this->dispatch('notify', message: "Theme '{$themeName}' updated successfully", type: 'success');
    }

    public function confirmDeleteTheme($themeId)
    {
        $this->themeToDelete = Theme::find($themeId);
        if (! $this->themeToDelete) {
            $this->dispatch('notify', message: __('Theme not found'), type: 'error');

            return;
        }

        // Check if theme has pages and show appropriate warning
        $pageCount = $this->themeToDelete->pages()->count();
        if ($pageCount > 0) {
            // For themes with many pages, show a more prominent warning
            if ($pageCount > 10) {
                $this->dispatch('notify',
                    message: __('Warning: This theme has :count pages that will be permanently deleted!', ['count' => $pageCount]),
                    type: 'warning'
                );
            }
        }

        $this->showDeleteModal = true;
    }

    public function deleteTheme()
    {
        if (! $this->themeToDelete) {
            $this->dispatch('notify', message: __('No theme selected for deletion'), type: 'error');

            return;
        }

        try {
            $themeName = $this->themeToDelete->name;
            $themeId = $this->themeToDelete->id;

            // Get the count of pages that will be deleted
            $pageCount = $this->themeToDelete->pages()->count();

            // Use a transaction to ensure data consistency
            DB::transaction(function () use ($pageCount) {
                // Delete all associated pages first
                if ($pageCount > 0) {
                    $this->themeToDelete->pages()->delete();
                }

                // Delete the theme
                $this->themeToDelete->delete();
            });

            // Clear default if this was the one
            if ($this->defaultThemeId == $themeId) {
                Setting::setDefaultThemeId(null);
                $this->defaultThemeId = null;
            }

            $this->loadThemes();

            if ($pageCount > 0) {
                $this->dispatch('notify',
                    message: __("Theme ':name' and :count associated page(s) deleted successfully.", ['name' => $themeName, 'count' => $pageCount]),
                    type: 'success'
                );
            } else {
                $this->dispatch('notify', message: __("Theme ':name' deleted successfully", ['name' => $themeName]), type: 'success');
            }

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
        $theme = Theme::with(['pages' => function ($query) {
            $query->select('id', 'key', 'components', 'theme_id', 'is_block', 'created_at', 'updated_at');
        }])->find($themeId);

        if (! $theme) {
            $this->dispatch('notify', message: __('Theme not found'), type: 'error');

            return;
        }

        $exportData = [
            'name' => $theme->name,
            'description' => $theme->description,
            'pages' => $theme->pages->map(function ($page) {
                return [
                    'key' => $page->key,
                    'components' => $page->components ?? [],
                    'is_block' => $page->is_block ?? false,
                    'created_at' => $page->created_at,
                    'updated_at' => $page->updated_at,
                ];
            })->toArray(),
            'exported_at' => now()->toISOString(),
            'version' => '1.0',
        ];

        // Log export summary
        $totalPages = count($exportData['pages']);
        $pagesWithComponents = count(array_filter($exportData['pages'], function ($page) {
            return ! empty($page['components']);
        }));

        Log::info('Theme export completed', [
            'theme_name' => $theme->name,
            'total_pages' => $totalPages,
            'pages_with_components' => $pagesWithComponents,
            'pages_without_components' => $totalPages - $pagesWithComponents,
        ]);

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
            // Create the cloned theme
            $clonedTheme = Theme::create([
                'name' => $this->cloneName,
                'description' => $this->themeToClone->description,
            ]);

            // Clone all pages associated with the original theme
            $originalPages = $this->themeToClone->pages;
            $clonedPagesCount = 0;

            foreach ($originalPages as $page) {
                // Ensure components is an array
                $components = $page->components ?? [];
                if (! is_array($components)) {
                    Log::warning('Invalid components format for page during cloning', [
                        'page_key' => $page->key,
                        'components_type' => gettype($components),
                        'components_value' => $components,
                    ]);
                    $components = [];
                }

                $clonedTheme->pages()->create([
                    'key' => $page->key,
                    'components' => $components,
                    'is_block' => $page->is_block ?? false,
                ]);
                $clonedPagesCount++;
            }

            $this->loadThemes();
            $this->closeCloneModal();

            $message = __("Theme ':name' created successfully as a copy of ':originalName'", [
                'name' => $clonedTheme->name,
                'originalName' => $this->themeToClone->name,
            ]);
            if ($clonedPagesCount > 0) {
                $message .= ' '.__('with :count page(s)', ['count' => $clonedPagesCount]);
            }

            $this->dispatch('notify', message: $message, type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('notify', message: __('Clone failed: :message', ['message' => $e->getMessage()]), type: 'error');
        }
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
                throw new \Exception(__('Invalid JSON format'));
            }

            // Validate required fields
            if (! isset($data['name']) || ! isset($data['pages'])) {
                throw new \Exception(__('Invalid theme file format'));
            }

            // Validate pages structure
            if (! is_array($data['pages'])) {
                throw new \Exception(__('Invalid pages format in theme file'));
            }

            foreach ($data['pages'] as $index => $pageData) {
                if (! isset($pageData['key'])) {
                    throw new \Exception(__('Invalid page data at index :index: missing key', ['index' => $index]));
                }
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
            $importedPagesCount = 0;
            $pagesWithComponents = 0;
            foreach ($data['pages'] as $pageData) {
                // Handle backward compatibility: check for both 'components' and 'content' fields
                $components = $pageData['components'] ?? $pageData['content'] ?? [];

                // Count pages with components
                if (! empty($components)) {
                    $pagesWithComponents++;
                }

                // Log the components data for debugging
                Log::info('Importing page', [
                    'page_key' => $pageData['key'],
                    'has_components' => ! empty($components),
                    'components_count' => count($components),
                ]);

                $theme->pages()->create([
                    'key' => $pageData['key'],
                    'components' => $components,
                    'is_block' => $pageData['is_block'] ?? false,
                ]);
                $importedPagesCount++;
            }

            // Log summary
            Log::info('Theme import completed', [
                'theme_name' => $theme->name,
                'total_pages' => $importedPagesCount,
                'pages_with_components' => $pagesWithComponents,
                'pages_without_components' => $importedPagesCount - $pagesWithComponents,
            ]);

            $this->loadThemes();
            $this->closeImportModal();

            $this->dispatch('notify',
                message: __("Theme ':name' imported successfully with :count pages (including :componentsCount with components)", [
                    'name' => $theme->name,
                    'count' => $importedPagesCount,
                    'componentsCount' => $pagesWithComponents,
                ]),
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
