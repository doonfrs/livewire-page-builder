<?php

/**
 * Theme Encryption Usage Examples
 *
 * This file demonstrates how to use the theme encryption functionality
 * in the Livewire Page Builder package. The encryption is completely
 * transparent to end users - they see the same UI but themes are
 * automatically encrypted when encryption is enabled.
 */

use Trinavo\LivewirePageBuilder\Facades\ThemeEncryptionService;
use Trinavo\LivewirePageBuilder\Facades\ThemeService;

// Example 1: Basic encryption setup and usage
echo "=== Example 1: Basic Encryption Setup ===\n";

// Check if encryption is enabled
$isEnabled = ThemeEncryptionService::isEncryptionEnabled();
echo 'Encryption enabled: '.($isEnabled ? 'Yes' : 'No')."\n";

if (! $isEnabled) {
    echo "Please enable encryption in your config or .env file\n";
    echo "Set PAGE_BUILDER_ENCRYPTION_ENABLED=true\n";
    echo "Set PAGE_BUILDER_ENCRYPTION_KEY=your_base64_encoded_key\n";
    exit;
}

// Example 2: Dynamic configuration
echo "\n=== Example 2: Dynamic Configuration ===\n";

// Change settings at runtime
ThemeEncryptionService::setEncryptionAlgorithm('AES-256-GCM');
ThemeEncryptionService::setFileExtension('.secure');

echo 'Algorithm: '.ThemeEncryptionService::getEncryptionAlgorithm()."\n";
echo 'File extension: '.ThemeEncryptionService::getFileExtension()."\n";

// Example 3: Transparent encryption export
echo "\n=== Example 3: Transparent Encryption Export ===\n";

try {
    // Export theme - automatically encrypted if encryption is enabled
    // Users see the same export button, but themes are encrypted behind the scenes
    $exportData = ThemeService::exportThemeAsJson(1);

    if ($exportData) {
        echo "Theme exported successfully\n";
        echo 'Data length: '.strlen($exportData)." characters\n";

        // Check if the export was encrypted
        if (ThemeEncryptionService::isEncrypted($exportData)) {
            echo "Export was automatically encrypted\n";
        } else {
            echo "Export was not encrypted\n";
        }

        // Save exported theme to file
        $extension = ThemeEncryptionService::getFileExtension();
        $filename = 'theme-'.date('Y-m-d-H-i-s').$extension;
        file_put_contents($filename, $exportData);
        echo "Theme saved to: $filename\n";

        // Example 4: Transparent import
        echo "\n=== Example 4: Transparent Import ===\n";

        // Import theme - automatically detected and decrypted if encrypted
        // Users see the same import button, but encrypted files are handled automatically
        $importedTheme = ThemeService::importThemeFromFile($filename);

        if ($importedTheme) {
            echo 'Theme imported successfully: '.$importedTheme->name."\n";
            echo 'Pages imported: '.$importedTheme->pages()->count()."\n";

            // Clean up - delete the imported theme
            $importedTheme->pages()->delete();
            $importedTheme->delete();
            echo "Imported theme cleaned up\n";
        } else {
            echo "Failed to import theme\n";
        }

        // Clean up exported file
        unlink($filename);
        echo "Exported file cleaned up\n";

    } else {
        echo "Failed to export theme\n";
    }

} catch (\Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}

// Example 5: File-based transparent encryption
echo "\n=== Example 5: File-based Transparent Encryption ===\n";

try {
    // Export to file - automatically encrypted if encryption is enabled
    $filePath = ThemeService::exportThemeToFile(1);

    if ($filePath) {
        echo "Theme exported to file: $filePath\n";

        // Check if the file was encrypted
        $content = file_get_contents($filePath);
        if (ThemeEncryptionService::isEncrypted($content)) {
            echo "File was automatically encrypted\n";
        } else {
            echo "File was not encrypted\n";
        }

        // Import from file - automatically detected and handled
        $fileImportedTheme = ThemeService::importThemeFromFile($filePath);

        if ($fileImportedTheme) {
            echo 'File-based import successful: '.$fileImportedTheme->name."\n";

            // Clean up
            $fileImportedTheme->pages()->delete();
            $fileImportedTheme->delete();
            echo "File-based theme cleaned up\n";
        }

        // Clean up exported file
        unlink($filePath);
        echo "Exported file cleaned up\n";
    }

} catch (\Exception $e) {
    echo 'File-based encryption error: '.$e->getMessage()."\n";
}

// Example 6: Encryption key management
echo "\n=== Example 6: Encryption Key Management ===\n";

// Generate a new encryption key
$newKey = ThemeEncryptionService::generateEncryptionKey();
echo 'Generated new key: '.substr($newKey, 0, 20)."...\n";

// Validate the key
$isValid = ThemeEncryptionService::validateEncryptionKey($newKey);
echo 'Key validation: '.($isValid ? 'Valid' : 'Invalid')."\n";

// Example 7: Check encryption status
echo "\n=== Example 7: Encryption Status ===\n";

echo 'Encryption enabled: '.(ThemeEncryptionService::isEncryptionEnabled() ? 'Yes' : 'No')."\n";
echo 'Algorithm: '.ThemeEncryptionService::getEncryptionAlgorithm()."\n";
echo 'File extension: '.ThemeEncryptionService::getFileExtension()."\n";
echo 'Password required: '.(ThemeEncryptionService::isPasswordRequired() ? 'Yes' : 'No')."\n";
echo 'Has encryption key: '.(! empty(ThemeEncryptionService::getEncryptionKey()) ? 'Yes' : 'No')."\n";

// Example 8: Error handling
echo "\n=== Example 8: Error Handling ===\n";

try {
    // Try to import with invalid data
    $wrongData = 'invalid_data';
    $wrongTheme = ThemeService::importThemeFromFile('nonexistent_file.txt');

    if (! $wrongTheme) {
        echo "Expected error: Import failed with invalid file\n";
    }

} catch (\Exception $e) {
    echo 'Expected error caught: '.$e->getMessage()."\n";
}

// Example 9: Reset configuration
echo "\n=== Example 9: Reset Configuration ===\n";

// Flush cache and reload from config
ThemeEncryptionService::flushCache();
echo "Configuration cache flushed and reloaded\n";

// Example 10: Demonstrate transparency
echo "\n=== Example 10: Demonstrate Transparency ===\n";

echo "For end users, the encryption is completely transparent:\n";
echo "1. User clicks 'Export Theme' button\n";
echo "2. Theme is automatically encrypted (if enabled) and downloaded\n";
echo "3. User sees no difference in the process\n";
echo "4. File extension automatically changes to .encrypted (if enabled)\n";
echo "5. Import automatically detects encrypted files and handles them\n";
echo "6. User sees the same import/export workflow\n";

// Example 11: File input behavior
echo "\n=== Example 11: File Input Behavior ===\n";

echo "The import dialog now accepts any file type:\n";
echo "- .json files: Regular theme files (no encryption)\n";
echo "- .encrypted files: Encrypted theme files (or custom extension)\n";
echo "- Any other file: System attempts to read and detect content\n";
echo "\nThe system automatically detects the file type by checking content:\n";
echo "- Regular JSON: {\"name\":\"Theme\",\"pages\":[...]}\n";
echo "- Encrypted: {\"encrypted\":true,\"version\":\"1.0\",\"data\":\"...\"}\n";
echo "\nNote: File input no longer restricts file types - users can select any file.\n";
echo "Content validation happens after file selection.\n";

echo "\n=== Examples Completed ===\n";
echo "The encryption works automatically in the background!\n";
echo "Users see the exact same UI, but themes are secured when encryption is enabled.\n";
echo "File input now accepts both .json and encrypted files seamlessly.\n";
echo "Check the documentation for more details on the transparent encryption system.\n";
