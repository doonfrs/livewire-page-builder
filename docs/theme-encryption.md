# Theme Encryption

The Livewire Page Builder package now supports encrypted theme exports and imports, providing an additional layer of security for your themes when sharing or storing them. **The encryption is completely transparent to users - they see the exact same UI and experience, but themes are automatically encrypted when encryption is enabled.**

## Features

- **Transparent Encryption**: Users never see encryption-related UI - it works automatically in the background
- **Configurable Encryption**: Enable/disable encryption via configuration
- **Multiple Algorithms**: Support for AES-256-CBC and AES-256-GCM
- **Dynamic Configuration**: Set encryption settings programmatically at runtime
- **Automatic Detection**: Automatically detects encrypted files during import
- **Secure Key Generation**: Built-in secure encryption key generation
- **Seamless Experience**: Same export/import buttons, same workflow, automatic encryption

## How It Works

### For Users (Frontend)

- **Export**: Click export button → theme is automatically encrypted if enabled
- **Import**: Upload file → automatically detected and decrypted if encrypted
- **UI**: No changes to the existing interface
- **Workflow**: Same export/import process as before

### For Developers (Backend)

- **Configuration**: Set encryption settings in config or programmatically
- **Automatic**: Encryption happens transparently when `exportTheme()` is called
- **Detection**: Import automatically detects encrypted vs. regular files
- **Control**: Full programmatic control over encryption behavior

## Configuration

### Environment Variables

Add these variables to your `.env` file:

```env
# Enable theme encryption
PAGE_BUILDER_ENCRYPTION_ENABLED=true

# Your encryption key (32-byte base64 encoded)
PAGE_BUILDER_ENCRYPTION_KEY=your_base64_encoded_32_byte_key_here

# Encryption algorithm (AES-256-CBC or AES-256-GCM)
PAGE_BUILDER_ENCRYPTION_ALGORITHM=AES-256-CBC

# File extension for encrypted themes
PAGE_BUILDER_ENCRYPTION_FILE_EXTENSION=.encrypted

# Whether to require password for encrypted themes
PAGE_BUILDER_ENCRYPTION_REQUIRE_PASSWORD=true
```

### Config File

The encryption settings are also configurable in `config/page-builder.php`:

```php
'encryption' => [
    'enabled' => env('PAGE_BUILDER_ENCRYPTION_ENABLED', false),
    'key' => env('PAGE_BUILDER_ENCRYPTION_KEY', ''),
    'algorithm' => env('PAGE_BUILDER_ENCRYPTION_ALGORITHM', 'AES-256-CBC'),
    'file_extension' => env('PAGE_BUILDER_ENCRYPTION_FILE_EXTENSION', '.encrypted'),
    'require_password' => env('PAGE_BUILDER_ENCRYPTION_REQUIRE_PASSWORD', true),
],
```

## User Experience

### Export Process

1. User clicks "Export Theme" button
2. If encryption is enabled: theme is automatically encrypted and downloaded with `.encrypted` extension
3. If encryption is disabled: theme is exported as regular JSON
4. User sees no difference in the process

### Import Process

1. User clicks "Import Theme" button
2. User selects file (encrypted or regular JSON)
3. System automatically detects file type and handles accordingly
4. User sees no difference in the process

### File Input Behavior

The import dialog now accepts any file type:

- **`.json` files**: Regular theme files (no encryption)
- **`.encrypted` files**: Encrypted theme files (or custom extension from config)
- **Any other file type**: The system will attempt to read and detect the content

Users can select any file, and the system will automatically:

- Detect if the file is encrypted by checking for `{"encrypted":true,"version":"1.0"` in the content
- Handle regular JSON files normally
- Decrypt encrypted files automatically using the configured encryption key
- Show appropriate error messages for invalid files

**Note**: The file input no longer restricts file types, allowing users to select any file. The system validates the content after selection.

### File Extensions

- **Regular themes**: `.json` extension
- **Encrypted themes**: `.encrypted` extension (or custom extension from config)
- **Automatic detection**: Import works with both file types seamlessly
- **File input**: The import dialog accepts both `.json` and encrypted file extensions

## Generating Encryption Keys

### Using the Service

```php
use Trinavo\LivewirePageBuilder\Facades\ThemeEncryptionService;

// Generate a secure 32-byte key
$key = ThemeEncryptionService::generateEncryptionKey();

// Validate a key
$isValid = ThemeEncryptionService::validateEncryptionKey($key);
```

### Using Artisan

```bash
# Generate a new encryption key
php artisan tinker
>>> Trinavo\LivewirePageBuilder\Facades\ThemeEncryptionService::generateEncryptionKey();
```

## Basic Usage

### For End Users

**No changes needed!** The encryption works automatically:

1. **Enable encryption** in your configuration
2. **Users export themes** using the same button
3. **Themes are automatically encrypted** and downloaded
4. **Users import themes** using the same button
5. **System automatically handles** encrypted vs. regular files

### For Developers

#### Using the Service Class

```php
use Trinavo\LivewirePageBuilder\Services\ThemeService;
use Trinavo\LivewirePageBuilder\Services\ThemeEncryptionService;

// Get the services
$themeService = app(ThemeService::class);
$encryptionService = app(ThemeEncryptionService::class);

// Check if encryption is enabled
if ($themeService->isEncryptionEnabled()) {
    // Export will automatically be encrypted
    $exportData = $themeService->exportThemeAsJson(1);
    
    // Import will automatically detect and decrypt
    $theme = $themeService->importThemeFromFile($filePath);
}
```

#### Using the Facades

```php
use Trinavo\LivewirePageBuilder\Facades\ThemeService;
use Trinavo\LivewirePageBuilder\Facades\ThemeEncryptionService;

// Check encryption status
$isEnabled = ThemeEncryptionService::isEncryptionEnabled();

// Export (automatically encrypted if enabled)
$exportData = ThemeService::exportThemeAsJson(1);

// Import (automatically detected and handled)
$theme = ThemeService::importThemeFromFile($filePath);
```

## Dynamic Configuration

You can change encryption settings at runtime:

```php
use Trinavo\LivewirePageBuilder\Facades\ThemeEncryptionService;

// Enable encryption
ThemeEncryptionService::setEncryptionEnabled(true);

// Set custom encryption key
ThemeEncryptionService::setEncryptionKey('your_custom_key');

// Change algorithm
ThemeEncryptionService::setEncryptionAlgorithm('AES-256-GCM');

// Set custom file extension
ThemeEncryptionService::setFileExtension('.secure');

// Configure password requirements
ThemeEncryptionService::setRequirePassword(false);

// Flush cache and reload from config
ThemeEncryptionService::flushCache();
```

## Exporting Themes

### Automatic Encryption

```php
use Trinavo\LivewirePageBuilder\Facades\ThemeService;

// This will automatically encrypt if encryption is enabled
$exportData = ThemeService::exportThemeAsJson(1);

// File extension will automatically be .encrypted if encryption is enabled
$filePath = ThemeService::exportThemeToFile(1);
```

### Manual Control (Advanced)

```php
use Trinavo\LivewirePageBuilder\Facades\ThemeService;

// Force encrypted export
$encryptedData = ThemeService::exportThemeAsEncryptedJson(1);

// Force encrypted file export
$filePath = ThemeService::exportThemeToEncryptedFile(1);
```

## Importing Themes

### Automatic Detection

```php
use Trinavo\LivewirePageBuilder\Facades\ThemeService;

// This automatically detects encrypted vs. regular files
$theme = ThemeService::importThemeFromFile($filePath);

// Works with both .json and .encrypted files
$theme = ThemeService::importThemeFromFile('theme.json');
$theme = ThemeService::importThemeFromFile('theme.encrypted');
```

### Manual Control (Advanced)

```php
use Trinavo\LivewirePageBuilder\Facades\ThemeService;

// Force encrypted import
$theme = ThemeService::importEncryptedTheme($encryptedData);

// Force encrypted file import
$theme = ThemeService::importThemeFromEncryptedFile($filePath);
```

## Encrypted File Format

Encrypted theme files have a special JSON structure:

```json
{
    "encrypted": true,
    "version": "1.0",
    "algorithm": "AES-256-CBC",
    "encrypted_at": "2024-01-15T10:30:00.000000Z",
    "data": "base64_encoded_encrypted_data_here"
}
```

## Security Considerations

### Key Management

- **Never commit encryption keys to version control**
- **Use environment variables for production keys**
- **Rotate keys regularly**
- **Use different keys for different environments**

### Password Security

- **Use strong, unique passwords for custom encryption**
- **Consider using a password manager for key storage**
- **Implement proper access controls for encrypted themes**

### Algorithm Selection

- **AES-256-CBC**: Good for compatibility, requires proper IV handling
- **AES-256-GCM**: Better security with authenticated encryption

## Error Handling

### Common Errors

```php
try {
    $theme = ThemeService::importThemeFromFile($filePath);
} catch (\Exception $e) {
    // Handle specific error types
    if (str_contains($e->getMessage(), 'Failed to decrypt')) {
        // Wrong password or corrupted data
    } elseif (str_contains($e->getMessage(), 'encryption is not enabled')) {
        // Encryption not configured
    }
}
```

### Validation

```php
use Trinavo\LivewirePageBuilder\Facades\ThemeEncryptionService;

// Check if data is encrypted
if (ThemeEncryptionService::isEncrypted($data)) {
    // Handle encrypted data
} else {
    // Handle regular JSON data
}

// Validate encryption key
if (!ThemeEncryptionService::validateEncryptionKey($key)) {
    throw new \InvalidArgumentException('Invalid encryption key format');
}
```

## Performance Considerations

- **Encryption/decryption adds processing overhead**
- **Large themes may take longer to process**
- **Consider caching decrypted themes for repeated access**
- **Use appropriate file size limits for uploads**

## Troubleshooting

### Common Issues

1. **"Encryption not enabled"**
   - Check `PAGE_BUILDER_ENCRYPTION_ENABLED` in `.env`
   - Verify encryption key is set

2. **"Failed to decrypt theme data"**
   - Verify the password/key is correct
   - Check if the file is actually encrypted
   - Ensure the encryption algorithm matches

3. **"Invalid encrypted theme format"**
   - File may be corrupted
   - Check if it's a valid encrypted theme file

### Debug Mode

Enable logging to debug encryption issues:

```php
// In your service provider or controller
Log::debug('Encryption settings', [
    'enabled' => ThemeEncryptionService::isEncryptionEnabled(),
    'algorithm' => ThemeEncryptionService::getEncryptionAlgorithm(),
    'has_key' => !empty(ThemeEncryptionService::getEncryptionKey()),
]);
```

## Examples

### Complete Export/Import Workflow

```php
use Trinavo\LivewirePageBuilder\Facades\ThemeService;
use Trinavo\LivewirePageBuilder\Facades\ThemeEncryptionService;

// 1. Configure encryption
ThemeEncryptionService::setEncryptionEnabled(true);
ThemeEncryptionService::setEncryptionKey('your_secure_key');

// 2. Export theme (automatically encrypted)
$exportData = ThemeService::exportThemeAsJson(1);

// 3. Store or transmit encrypted data
file_put_contents('secure_theme.encrypted', $exportData);

// 4. Import encrypted theme (automatically detected and decrypted)
$importedTheme = ThemeService::importThemeFromFile('secure_theme.encrypted');

echo "Imported theme: " . $importedTheme->name;
```

### Custom Encryption Service

```php
class CustomThemeEncryption
{
    public function encryptTheme($themeId)
    {
        // Custom encryption logic
        $themeService = app(ThemeService::class);
        
        // Will automatically encrypt if enabled
        return $themeService->exportThemeAsJson($themeId);
    }
    
    public function decryptTheme($filePath)
    {
        $themeService = app(ThemeService::class);
        
        // Will automatically detect and decrypt
        return $themeService->importThemeFromFile($filePath);
    }
}
```

## API Reference

### ThemeEncryptionService Methods

| Method | Description | Returns |
|--------|-------------|---------|
| `isEncryptionEnabled()` | Check if encryption is enabled | `bool` |
| `setEncryptionEnabled(bool)` | Enable/disable encryption | `self` |
| `setEncryptionKey(string)` | Set encryption key | `self` |
| `getEncryptionKey()` | Get current encryption key | `string` |
| `setEncryptionAlgorithm(string)` | Set encryption algorithm | `self` |
| `getEncryptionAlgorithm()` | Get current algorithm | `string` |
| `setFileExtension(string)` | Set file extension | `self` |
| `getFileExtension()` | Get current file extension | `string` |
| `setRequirePassword(bool)` | Set password requirement | `self` |
| `isPasswordRequired()` | Check password requirement | `bool` |
| `encryptThemeData(array, ?string)` | Encrypt theme data | `string|null` |
| `decryptThemeData(string, ?string)` | Decrypt theme data | `array|null` |
| `isEncrypted(string)` | Check if data is encrypted | `bool` |
| `generateEncryptionKey()` | Generate secure key | `string` |
| `validateEncryptionKey(string)` | Validate key format | `bool` |

### ThemeService Encryption Methods

| Method | Description | Returns |
|--------|-------------|---------|
| `exportThemeAsJson(int, int)` | Export with auto-encryption | `string|null` |
| `exportThemeToFile(int, ?string)` | Export to file with auto-encryption | `string|null` |
| `exportThemeToEncryptedFile(int, ?string, ?string)` | Force encrypted file export | `string|null` |
| `exportThemeAsEncryptedJson(int, ?string)` | Force encrypted JSON export | `string|null` |
| `importThemeFromFile(string, bool)` | Import with auto-detection | `Theme|null` |
| `importEncryptedTheme(string, bool, ?string)` | Force encrypted import | `Theme|null` |
| `importThemeFromEncryptedFile(string, bool, ?string)` | Force encrypted file import | `Theme|null` |
| `isEncryptionEnabled()` | Check encryption status | `bool` |
| `getEncryptionService()` | Get encryption service | `ThemeEncryptionService` |

## Migration from Previous Versions

If you're upgrading from a previous version:

1. **No breaking changes** - existing functionality remains intact
2. **Encryption is disabled by default** - opt-in feature
3. **Backward compatible** - regular JSON imports still work
4. **Gradual adoption** - enable encryption when ready
5. **UI unchanged** - users see the same interface

## Best Practices

1. **Start with encryption disabled** during development
2. **Test thoroughly** before enabling in production
3. **Document your encryption keys** securely
4. **Implement proper backup strategies** for encrypted themes
5. **Monitor performance** impact in production
6. **Regular security audits** of encryption implementation
7. **Keep encryption transparent** to end users
8. **Use environment-specific keys** for different deployments
