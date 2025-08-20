<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Trinavo\LivewirePageBuilder\Models\Theme;

class ThemeService
{
    /**
     * Export a theme to JSON format
     *
     * @param  int|Theme  $theme  Theme ID or Theme model instance
     * @return array|null Export data or null if theme not found
     */
    public function exportTheme(int|Theme $theme): ?array
    {
        $themeModel = $theme instanceof Theme ? $theme : Theme::find($theme);

        if (! $themeModel) {
            Log::warning('Theme export failed: Theme not found', ['theme_id' => $theme]);

            return null;
        }

        $themeWithPages = Theme::with(['pages' => function ($query) {
            $query->select('id', 'key', 'components', 'theme_id', 'is_block', 'created_at', 'updated_at');
        }])->find($themeModel->id);

        $exportData = [
            'name' => $themeWithPages->name,
            'description' => $themeWithPages->description,
            'pages' => $themeWithPages->pages->map(function ($page) {
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
            'theme_name' => $themeWithPages->name,
            'total_pages' => $totalPages,
            'pages_with_components' => $pagesWithComponents,
            'pages_without_components' => $totalPages - $pagesWithComponents,
        ]);

        return $exportData;
    }

    /**
     * Export a theme to JSON file and return the file path
     *
     * @param  int|Theme  $theme  Theme ID or Theme model instance
     * @param  string|null  $directory  Directory to save the file (defaults to storage/app/themes)
     * @return string|null File path or null if export failed
     */
    public function exportThemeToFile(int|Theme $theme, ?string $directory = null): ?string
    {
        $exportData = $this->exportTheme($theme);

        if (! $exportData) {
            return null;
        }

        $themeModel = $theme instanceof Theme ? $theme : Theme::find($theme);
        $directory = $directory ?: storage_path('app/themes');

        // Create directory if it doesn't exist
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $fileName = 'theme-'.Str::slug($themeModel->name).'-'.now()->format('Y-m-d-H-i-s').'.json';
        $filePath = $directory.'/'.$fileName;

        $jsonContent = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (file_put_contents($filePath, $jsonContent) === false) {
            Log::error('Failed to write theme export file', ['file_path' => $filePath]);

            return null;
        }

        Log::info('Theme exported to file', ['file_path' => $filePath]);

        return $filePath;
    }

    /**
     * Import a theme from JSON data
     *
     * @param  array  $data  Theme data array
     * @param  bool  $overwriteExisting  Whether to overwrite existing theme with same name
     * @return Theme|null Imported theme or null if import failed
     */
    public function importTheme(array $data, bool $overwriteExisting = false): ?Theme
    {
        try {
            // Validate required fields
            if (! isset($data['name']) || ! isset($data['pages'])) {
                throw new \Exception('Invalid theme file format: missing required fields');
            }

            // Validate pages structure
            if (! is_array($data['pages'])) {
                throw new \Exception('Invalid pages format in theme file');
            }

            foreach ($data['pages'] as $index => $pageData) {
                if (! isset($pageData['key'])) {
                    throw new \Exception("Invalid page data at index {$index}: missing key");
                }
            }

            // Check if theme name already exists
            $existingTheme = Theme::where('name', $data['name'])->first();

            if ($existingTheme) {
                if ($overwriteExisting) {
                    // Delete existing theme and all its pages
                    $existingTheme->pages()->delete();
                    $existingTheme->delete();
                } else {
                    // Generate unique name
                    $originalName = $data['name'];
                    $counter = 1;
                    do {
                        $data['name'] = $originalName.' ('.$counter.')';
                        $counter++;
                    } while (Theme::where('name', $data['name'])->exists());
                }
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

            return $theme;

        } catch (\Exception $e) {
            Log::error('Theme import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Import a theme from JSON file
     *
     * @param  string  $filePath  Path to the JSON file
     * @param  bool  $overwriteExisting  Whether to overwrite existing theme with same name
     * @return Theme|null Imported theme or null if import failed
     */
    public function importThemeFromFile(string $filePath, bool $overwriteExisting = false): ?Theme
    {
        try {
            if (! file_exists($filePath)) {
                throw new \Exception("File not found: {$filePath}");
            }

            $content = file_get_contents($filePath);
            if ($content === false) {
                throw new \Exception("Failed to read file: {$filePath}");
            }

            $data = json_decode($content, true);
            if (! $data) {
                throw new \Exception('Invalid JSON format');
            }

            return $this->importTheme($data, $overwriteExisting);

        } catch (\Exception $e) {
            Log::error('Theme import from file failed', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Clone an existing theme
     *
     * @param  int|Theme  $theme  Theme ID or Theme model instance
     * @param  string  $newName  Name for the cloned theme
     * @return Theme|null Cloned theme or null if cloning failed
     */
    public function cloneTheme(int|Theme $theme, string $newName): ?Theme
    {
        try {
            $themeModel = $theme instanceof Theme ? $theme : Theme::find($theme);

            if (! $themeModel) {
                throw new \Exception('Theme not found');
            }

            // Check if new name already exists
            if (Theme::where('name', $newName)->exists()) {
                throw new \Exception("Theme with name '{$newName}' already exists");
            }

            // Create new theme
            $clonedTheme = Theme::create([
                'name' => $newName,
                'description' => $themeModel->description,
            ]);

            // Clone all pages
            $clonedPagesCount = 0;
            foreach ($themeModel->pages as $page) {
                $clonedTheme->pages()->create([
                    'key' => $page->key,
                    'components' => $page->components,
                    'is_block' => $page->is_block,
                ]);
                $clonedPagesCount++;
            }

            Log::info('Theme cloned successfully', [
                'original_theme' => $themeModel->name,
                'cloned_theme' => $newName,
                'pages_cloned' => $clonedPagesCount,
            ]);

            return $clonedTheme;

        } catch (\Exception $e) {
            Log::error('Theme cloning failed', [
                'theme_id' => $theme instanceof Theme ? $theme->id : $theme,
                'new_name' => $newName,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get export data as JSON string
     *
     * @param  int|Theme  $theme  Theme ID or Theme model instance
     * @param  int  $jsonFlags  JSON encoding flags
     * @return string|null JSON string or null if export failed
     */
    public function exportThemeAsJson(int|Theme $theme, int $jsonFlags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): ?string
    {
        $exportData = $this->exportTheme($theme);

        if (! $exportData) {
            return null;
        }

        return json_encode($exportData, $jsonFlags);
    }
}
