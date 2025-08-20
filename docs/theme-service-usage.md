# Theme Service Usage

The `ThemeService` provides programmatic access to theme import, export, and cloning functionality. This allows you to integrate theme management directly into your application code.

## Basic Usage

### Using the Service Class

```php
use Trinavo\LivewirePageBuilder\Services\ThemeService;

// Get the service instance
$themeService = app(ThemeService::class);

// Or use dependency injection
public function someMethod(ThemeService $themeService)
{
    // Use the service
}
```

### Using the Facade

```php
use Trinavo\LivewirePageBuilder\Facades\ThemeService;

// Direct facade usage
ThemeService::exportTheme(1);
```

## Exporting Themes

### Export to Array

```php
// Export theme by ID
$exportData = $themeService->exportTheme(1);

// Export theme by model instance
$theme = Theme::find(1);
$exportData = $themeService->exportTheme($theme);

if ($exportData) {
    // $exportData contains:
    // - name: Theme name
    // - description: Theme description
    // - pages: Array of pages with components
    // - exported_at: Export timestamp
    // - version: Export version
}
```

### Export to File

```php
// Export to default directory (storage/app/themes)
$filePath = $themeService->exportThemeToFile(1);

// Export to custom directory
$filePath = $themeService->exportThemeToFile(1, storage_path('custom/themes'));

if ($filePath) {
    // File was created successfully
    // $filePath contains the full path to the exported JSON file
}
```

### Export as JSON String

```php
// Get JSON string with default formatting
$jsonString = $themeService->exportThemeAsJson(1);

// Get compact JSON string
$jsonString = $themeService->exportThemeAsJson(1, JSON_UNESCAPED_UNICODE);
```

## Importing Themes

### Import from Array Data

```php
$themeData = [
    'name' => 'My New Theme',
    'description' => 'A custom theme',
    'pages' => [
        [
            'key' => 'home',
            'components' => [
                // Your page components here
            ],
            'is_block' => false
        ]
    ]
];

// Import with auto-rename if name exists
$importedTheme = $themeService->importTheme($themeData);

// Import with overwrite if name exists
$importedTheme = $themeService->importTheme($themeData, true);

if ($importedTheme) {
    // Theme imported successfully
    echo "Imported theme: " . $importedTheme->name;
}
```

### Import from File

```php
// Import from JSON file
$filePath = storage_path('themes/my-theme.json');
$importedTheme = $themeService->importThemeFromFile($filePath);

// Import with overwrite
$importedTheme = $themeService->importThemeFromFile($filePath, true);
```

## Cloning Themes

```php
// Clone a theme with a new name
$clonedTheme = $themeService->cloneTheme(1, 'My Cloned Theme');

if ($clonedTheme) {
    // Theme cloned successfully
    echo "Cloned theme: " . $clonedTheme->name;
}
```

## Error Handling

All methods return `null` on failure and log errors. You can check for errors:

```php
$result = $themeService->exportTheme(999); // Non-existent theme

if ($result === null) {
    // Operation failed, check logs for details
    Log::error('Theme operation failed');
}
```

## Complete Example

Here's a complete example showing how to use the ThemeService in a controller:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Trinavo\LivewirePageBuilder\Services\ThemeService;
use Trinavo\LivewirePageBuilder\Models\Theme;

class ThemeController extends Controller
{
    public function __construct(
        private ThemeService $themeService
    ) {}

    public function export(Request $request): JsonResponse
    {
        $themeId = $request->input('theme_id');
        
        $exportData = $this->themeService->exportTheme($themeId);
        
        if (!$exportData) {
            return response()->json(['error' => 'Theme not found'], 404);
        }
        
        return response()->json($exportData);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'theme_data' => 'required|array',
            'overwrite' => 'boolean'
        ]);
        
        $overwrite = $request->boolean('overwrite', false);
        $themeData = $request->input('theme_data');
        
        $importedTheme = $this->themeService->importTheme($themeData, $overwrite);
        
        if (!$importedTheme) {
            return response()->json(['error' => 'Import failed'], 500);
        }
        
        return response()->json([
            'message' => 'Theme imported successfully',
            'theme' => $importedTheme
        ]);
    }

    public function clone(Request $request): JsonResponse
    {
        $request->validate([
            'theme_id' => 'required|integer',
            'new_name' => 'required|string|max:255'
        ]);
        
        $themeId = $request->input('theme_id');
        $newName = $request->input('new_name');
        
        $clonedTheme = $this->themeService->cloneTheme($themeId, $newName);
        
        if (!$clonedTheme) {
            return response()->json(['error' => 'Cloning failed'], 500);
        }
        
        return response()->json([
            'message' => 'Theme cloned successfully',
            'theme' => $clonedTheme
        ]);
    }
}
```

## API Routes Example

```php
// routes/api.php
Route::prefix('themes')->group(function () {
    Route::post('export', [ThemeController::class, 'export']);
    Route::post('import', [ThemeController::class, 'import']);
    Route::post('clone', [ThemeController::class, 'clone']);
});
```

## Artisan Command Example

You can also create Artisan commands using the ThemeService:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Trinavo\LivewirePageBuilder\Services\ThemeService;

class ExportThemeCommand extends Command
{
    protected $signature = 'theme:export {theme_id} {--output=}';
    protected $description = 'Export a theme to JSON file';

    public function handle(ThemeService $themeService): int
    {
        $themeId = $this->argument('theme_id');
        $outputPath = $this->option('output');
        
        if ($outputPath) {
            $filePath = $themeService->exportThemeToFile($themeId, dirname($outputPath));
            $fileName = basename($outputPath);
            
            if ($filePath && rename($filePath, $outputPath)) {
                $this->info("Theme exported to: {$outputPath}");
                return 0;
            }
        } else {
            $exportData = $themeService->exportTheme($themeId);
            if ($exportData) {
                $this->info(json_encode($exportData, JSON_PRETTY_PRINT));
                return 0;
            }
        }
        
        $this->error('Failed to export theme');
        return 1;
    }
}
```

## Notes

- All operations are logged for debugging purposes
- The service handles both theme IDs and Theme model instances
- File operations create directories automatically if they don't exist
- Import operations can handle both `components` and `content` fields for backward compatibility
- Cloning creates exact copies of themes with all their pages and components
