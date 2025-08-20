<?php

/**
 * Example usage of the ThemeService
 *
 * This file demonstrates how to use the ThemeService for theme import/export operations
 * in your Laravel application.
 */

use Trinavo\LivewirePageBuilder\Facades\ThemeService as ThemeServiceFacade;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Services\ThemeService;

// Example 1: Using the service class directly
$themeService = app(ThemeService::class);

// Export a theme to array
$exportData = $themeService->exportTheme(1);
if ($exportData) {
    echo 'Theme exported: '.$exportData['name']."\n";
    echo 'Pages count: '.count($exportData['pages'])."\n";
}

// Export a theme to file
$filePath = $themeService->exportThemeToFile(1);
if ($filePath) {
    echo 'Theme exported to: '.$filePath."\n";
}

// Example 2: Using the facade
$jsonString = ThemeServiceFacade::exportThemeAsJson(1);
if ($jsonString) {
    echo "JSON export successful\n";
}

// Example 3: Import a theme from array data
$themeData = [
    'name' => 'My Custom Theme',
    'description' => 'A theme created programmatically',
    'pages' => [
        [
            'key' => 'home',
            'components' => [
                [
                    'type' => 'row',
                    'properties' => [
                        'background_color' => '#f3f4f6',
                    ],
                    'children' => [
                        [
                            'type' => 'column',
                            'properties' => [
                                'width' => '12',
                            ],
                            'children' => [
                                [
                                    'type' => 'text',
                                    'properties' => [
                                        'content' => 'Welcome to my custom theme!',
                                        'text_align' => 'center',
                                        'font_size' => '2xl',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'is_block' => false,
        ],
    ],
];

$importedTheme = $themeService->importTheme($themeData);
if ($importedTheme) {
    echo 'Theme imported successfully: '.$importedTheme->name."\n";
}

// Example 4: Clone an existing theme
$clonedTheme = $themeService->cloneTheme(1, 'Cloned Theme');
if ($clonedTheme) {
    echo 'Theme cloned successfully: '.$clonedTheme->name."\n";
}

// Example 5: Import from file
$importedFromFile = $themeService->importThemeFromFile(storage_path('themes/example-theme.json'));
if ($importedFromFile) {
    echo 'Theme imported from file: '.$importedFromFile->name."\n";
}

// Example 6: Overwrite existing theme
$overwriteTheme = $themeService->importTheme($themeData, true); // true = overwrite existing
if ($overwriteTheme) {
    echo 'Theme overwritten: '.$overwriteTheme->name."\n";
}

// Example 7: Get theme by model instance
$theme = Theme::find(1);
if ($theme) {
    $exportData = $themeService->exportTheme($theme);
    if ($exportData) {
        echo 'Theme exported via model: '.$exportData['name']."\n";
    }
}

// Example 8: Error handling
$result = $themeService->exportTheme(999); // Non-existent theme
if ($result === null) {
    echo "Export failed - check logs for details\n";
}

// Example 9: Custom export directory
$customPath = $themeService->exportThemeToFile(1, storage_path('custom/themes'));
if ($customPath) {
    echo 'Theme exported to custom directory: '.$customPath."\n";
}

// Example 10: Compact JSON export
$compactJson = $themeService->exportThemeAsJson(1, JSON_UNESCAPED_UNICODE);
if ($compactJson) {
    echo "Compact JSON export successful\n";
}
