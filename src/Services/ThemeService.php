<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Trinavo\LivewirePageBuilder\Exceptions\InvalidThemeFormatException;
use Trinavo\LivewirePageBuilder\Exceptions\ThemeEncryptionException;
use Trinavo\LivewirePageBuilder\Exceptions\ThemeFileException;
use Trinavo\LivewirePageBuilder\Models\Theme;

class ThemeService
{
    protected ThemeEncryptionService $encryptionService;

    /**
     * Create a new theme service instance
     */
    public function __construct(ThemeEncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

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

        Log::debug('Theme export completed', [
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

        // If encryption is enabled, use encrypted extension and encrypt the content
        if ($this->encryptionService->isEncryptionEnabled()) {
            $extension = $this->encryptionService->getFileExtension();
            $fileName = 'theme-'.Str::slug($themeModel->name).'-'.now()->format('Y-m-d-H-i-s').$extension;
            $filePath = $directory.'/'.$fileName;

            // Encrypt the theme data
            $encryptedContent = $this->encryptionService->encryptThemeData($exportData);

            if (! $encryptedContent) {
                Log::error('Failed to encrypt theme data for export');

                return null;
            }

            if (file_put_contents($filePath, $encryptedContent) === false) {
                Log::error('Failed to write encrypted theme export file', ['file_path' => $filePath]);

                return null;
            }

            Log::debug('Encrypted theme exported to file', [
                'file_path' => $filePath,
                'algorithm' => $this->encryptionService->getEncryptionAlgorithm(),
            ]);

            return $filePath;
        }

        // Fall back to regular JSON export if encryption is disabled
        $fileName = 'theme-'.Str::slug($themeModel->name).'-'.now()->format('Y-m-d-H-i-s').'.json';
        $filePath = $directory.'/'.$fileName;

        $jsonContent = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (file_put_contents($filePath, $jsonContent) === false) {
            Log::error('Failed to write theme export file', ['file_path' => $filePath]);

            return null;
        }

        Log::debug('Theme exported to file', ['file_path' => $filePath]);

        return $filePath;
    }

    /**
     * Export a theme to encrypted file and return the file path
     *
     * @param  int|Theme  $theme  Theme ID or Theme model instance
     * @param  string|null  $directory  Directory to save the file (defaults to storage/app/themes)
     * @param  string|null  $password  Custom password for encryption (optional)
     * @return string|null File path or null if export failed
     */
    public function exportThemeToEncryptedFile(int|Theme $theme, ?string $directory = null, ?string $password = null): ?string
    {
        if (! $this->encryptionService->isEncryptionEnabled()) {
            Log::warning('Theme encryption export attempted but encryption is not enabled');

            return null;
        }

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

        $extension = $this->encryptionService->getFileExtension();
        $fileName = 'theme-'.Str::slug($themeModel->name).'-'.now()->format('Y-m-d-H-i-s').$extension;
        $filePath = $directory.'/'.$fileName;

        // Encrypt the theme data
        $encryptedContent = $this->encryptionService->encryptThemeData($exportData, $password);

        if (! $encryptedContent) {
            Log::error('Failed to encrypt theme data for export');

            return null;
        }

        if (file_put_contents($filePath, $encryptedContent) === false) {
            Log::error('Failed to write encrypted theme export file', ['file_path' => $filePath]);

            return null;
        }

        Log::debug('Encrypted theme exported to file', [
            'file_path' => $filePath,
            'algorithm' => $this->encryptionService->getEncryptionAlgorithm(),
        ]);

        return $filePath;
    }

    /**
     * Export a theme as encrypted JSON string
     *
     * @param  int|Theme  $theme  Theme ID or Theme model instance
     * @param  string|null  $password  Custom password for encryption (optional)
     * @return string|null Encrypted JSON string or null if export failed
     */
    public function exportThemeAsEncryptedJson(int|Theme $theme, ?string $password = null): ?string
    {
        if (! $this->encryptionService->isEncryptionEnabled()) {
            Log::warning('Theme encryption export attempted but encryption is not enabled');

            return null;
        }

        $exportData = $this->exportTheme($theme);

        if (! $exportData) {
            return null;
        }

        return $this->encryptionService->encryptThemeData($exportData, $password);
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
        // Validate required fields
        if (! isset($data['name']) || ! isset($data['pages'])) {
            throw new InvalidThemeFormatException(__('The selected file does not appear to be a valid theme file. It is missing required fields (name or pages).'));
        }

        // Validate pages structure
        if (! is_array($data['pages'])) {
            throw new InvalidThemeFormatException(__('The selected file contains invalid page data. Please ensure it is a properly formatted theme file.'));
        }

        foreach ($data['pages'] as $index => $pageData) {
            if (! isset($pageData['key'])) {
                throw new InvalidThemeFormatException(__('The selected file contains invalid page data. Some pages are missing required information.'));
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
            Log::debug('Importing page', [
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
        Log::debug('Theme import completed', [
            'theme_name' => $theme->name,
            'total_pages' => $importedPagesCount,
            'pages_with_components' => $pagesWithComponents,
            'pages_without_components' => $importedPagesCount - $pagesWithComponents,
        ]);

        return $theme;

    }

    /**
     * Import a theme from encrypted data
     *
     * @param  string  $encryptedData  Encrypted theme data
     * @param  bool  $overwriteExisting  Whether to overwrite existing theme with same name
     * @param  string|null  $password  Custom password for decryption (optional)
     * @return Theme|null Imported theme or null if import failed
     */
    public function importEncryptedTheme(string $encryptedData, bool $overwriteExisting = false, ?string $password = null): ?Theme
    {
        // Decrypt the theme data (encryption setting doesn't affect ability to decrypt)
        $themeData = $this->encryptionService->decryptThemeData($encryptedData, $password);

        if (! $themeData) {
            // Check if the issue is with the encryption key
            if (empty($password) && empty($this->encryptionService->getEncryptionKey())) {
                throw new ThemeEncryptionException(__('This file is encrypted but no encryption key is configured. Please contact your administrator to set up the encryption key.'));
            }

            throw new ThemeEncryptionException(__('This file is encrypted but cannot be decrypted. The encryption key may be incorrect or the file may be corrupted.'));
        }

        // Import the decrypted theme
        return $this->importTheme($themeData, $overwriteExisting);
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
        if (! file_exists($filePath)) {
            throw new ThemeFileException(__('This file could not be found. Please ensure the file exists and try again.'));
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new ThemeFileException(__('Unable to read the selected file. Please ensure the file is not corrupted and try again.'));
        }

        // Check if the file is encrypted
        if ($this->encryptionService->isEncrypted($content)) {
            Log::debug('Detected encrypted theme file, attempting decryption');

            return $this->importEncryptedTheme($content, $overwriteExisting);
        }

        $data = json_decode($content, true);
        if (! $data) {
            throw new InvalidThemeFormatException(__('The selected file does not contain valid JSON data. Please select a valid theme file.'));
        }

        return $this->importTheme($data, $overwriteExisting);
    }

    /**
     * Import a theme from encrypted file
     *
     * @param  string  $filePath  Path to the encrypted file
     * @param  bool  $overwriteExisting  Whether to overwrite existing theme with same name
     * @param  string|null  $password  Custom password for decryption (optional)
     * @return Theme|null Imported theme or null if import failed
     */
    public function importThemeFromEncryptedFile(string $filePath, bool $overwriteExisting = false, ?string $password = null): ?Theme
    {
        if (! file_exists($filePath)) {
            throw new ThemeFileException(__('This file could not be found. Please ensure the file exists and try again.'));
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new ThemeFileException(__('Unable to read the selected file. Please ensure the file is not corrupted and try again.'));
        }

        return $this->importEncryptedTheme($content, $overwriteExisting, $password);

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

        Log::debug('Theme cloned successfully', [
            'original_theme' => $themeModel->name,
            'cloned_theme' => $newName,
            'pages_cloned' => $clonedPagesCount,
        ]);

        return $clonedTheme;

    }

    /**
     * Replace all pages in an existing theme with new pages data
     *
     * This method deletes all existing pages in the theme and creates new ones
     * from the provided pages data, keeping the same theme ID.
     *
     * @param  Theme  $theme  The theme to update
     * @param  array  $pagesData  Array of page data to import
     * @return int Number of pages imported
     */
    public function replacePagesInTheme(Theme $theme, array $pagesData): int
    {
        // Validate pages structure
        foreach ($pagesData as $index => $pageData) {
            if (! isset($pageData['key'])) {
                throw new \Exception("Invalid page data at index {$index}: missing key");
            }
        }

        // Delete all existing pages for this theme
        $deletedCount = $theme->pages()->count();
        $theme->pages()->delete();

        Log::debug('Deleted existing pages for theme', [
            'theme_id' => $theme->id,
            'theme_name' => $theme->name,
            'deleted_pages_count' => $deletedCount,
        ]);

        // Import new pages
        $importedPagesCount = 0;
        $pagesWithComponents = 0;

        foreach ($pagesData as $pageData) {
            // Handle backward compatibility: check for both 'components' and 'content' fields
            $components = $pageData['components'] ?? $pageData['content'] ?? [];

            // Count pages with components
            if (! empty($components)) {
                $pagesWithComponents++;
            }

            $theme->pages()->create([
                'key' => $pageData['key'],
                'components' => $components,
                'is_block' => $pageData['is_block'] ?? false,
            ]);

            $importedPagesCount++;
        }

        Log::debug('Theme pages replaced successfully', [
            'theme_id' => $theme->id,
            'theme_name' => $theme->name,
            'pages_deleted' => $deletedCount,
            'pages_imported' => $importedPagesCount,
            'pages_with_components' => $pagesWithComponents,
        ]);

        return $importedPagesCount;
    }

    /**
     * Replace only selected pages in an existing theme
     *
     * This method only deletes and replaces pages with keys matching the provided list.
     * Other pages in the theme remain untouched.
     *
     * @param  Theme  $theme  The theme to update
     * @param  array  $pagesData  Array of page data to import
     * @param  array  $pageKeys  Array of page keys to import (only these will be affected)
     * @return int Number of pages imported
     */
    public function replaceSelectedPagesInTheme(Theme $theme, array $pagesData, array $pageKeys): int
    {
        // Validate pages structure
        foreach ($pagesData as $index => $pageData) {
            if (! isset($pageData['key'])) {
                throw new \Exception("Invalid page data at index {$index}: missing key");
            }
        }

        // Delete only pages with matching keys
        $deletedCount = $theme->pages()->whereIn('key', $pageKeys)->count();
        $theme->pages()->whereIn('key', $pageKeys)->delete();

        Log::debug('Deleted selected pages for theme', [
            'theme_id' => $theme->id,
            'theme_name' => $theme->name,
            'selected_keys' => $pageKeys,
            'deleted_pages_count' => $deletedCount,
        ]);

        // Import only pages that match the selected keys
        $importedPagesCount = 0;
        $pagesWithComponents = 0;

        foreach ($pagesData as $pageData) {
            // Only import if this page's key is in the selected list
            if (! in_array($pageData['key'], $pageKeys)) {
                continue;
            }

            // Handle backward compatibility: check for both 'components' and 'content' fields
            $components = $pageData['components'] ?? $pageData['content'] ?? [];

            // Count pages with components
            if (! empty($components)) {
                $pagesWithComponents++;
            }

            $theme->pages()->create([
                'key' => $pageData['key'],
                'components' => $components,
                'is_block' => $pageData['is_block'] ?? false,
            ]);

            $importedPagesCount++;
        }

        Log::debug('Selected theme pages replaced successfully', [
            'theme_id' => $theme->id,
            'theme_name' => $theme->name,
            'pages_deleted' => $deletedCount,
            'pages_imported' => $importedPagesCount,
            'pages_with_components' => $pagesWithComponents,
        ]);

        return $importedPagesCount;
    }

    /**
     * Export a theme to JSON format with automatic encryption if enabled
     *
     * @param  int|Theme  $theme  Theme ID or Theme model instance
     * @return string|null JSON string or null if export failed
     */
    public function exportThemeAsJson(int|Theme $theme, int $jsonFlags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): ?string
    {
        // If encryption is enabled, automatically encrypt the export
        if ($this->encryptionService->isEncryptionEnabled()) {
            $exportData = $this->exportTheme($theme);

            if (! $exportData) {
                return null;
            }

            // Automatically encrypt using the default key (transparent to user)
            return $this->encryptionService->encryptThemeData($exportData);
        }

        // Fall back to regular JSON export if encryption is disabled
        $exportData = $this->exportTheme($theme);

        if (! $exportData) {
            return null;
        }

        return json_encode($exportData, $jsonFlags);
    }

    /**
     * Check if theme encryption is enabled
     */
    public function isEncryptionEnabled(): bool
    {
        return $this->encryptionService->isEncryptionEnabled();
    }

    /**
     * Get the encryption service instance
     */
    public function getEncryptionService(): ThemeEncryptionService
    {
        return $this->encryptionService;
    }
}
