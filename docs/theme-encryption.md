# Theme Encryption

The package can encrypt theme exports so that the JSON payload — names, descriptions, page components, the whole tree — is unreadable without the configured key. Encryption is **opt‑in**, transparent (the editor UI is unchanged), and uses **AES‑256‑GCM** exclusively.

> ℹ️ Older versions of the package supported AES‑256‑CBC as well. That option has been removed (commit `51ec732`, May 2026). The package now only emits and accepts GCM. Files encrypted with the old CBC pipeline cannot be imported — re‑export them from the original installation before upgrading, or roll back to decrypt them first.

---

## How it works

- **Algorithm**: AES‑256‑GCM (authenticated encryption). The 16‑byte GCM tag means tampering with the ciphertext, IV, or tag is detected on decryption — a wrong key, a truncated file, or any modification fails cleanly instead of returning garbage.
- **Ciphertext layout**: `base64(iv || tag || ciphertext)` where `iv` is 12 bytes and `tag` is 16 bytes.
- **Envelope**: encrypted exports are wrapped in a small JSON object so the package can auto‑detect them on import:

  ```json
  {
      "encrypted":    true,
      "version":      "1.0",
      "encrypted_at": "2026-05-14T...",
      "data":         "<base64 ciphertext>"
  }
  ```

  The algorithm is not embedded in the file — the package always uses AES‑256‑GCM.
- **Detection**: any import that contains `"encrypted": true` is treated as encrypted; everything else is parsed as plain JSON. You don't need to choose between code paths.

---

## Configuration

### `.env`

```env
PAGE_BUILDER_ENCRYPTION_ENABLED=true
PAGE_BUILDER_ENCRYPTION_KEY=<base64-encoded 32-byte key>
PAGE_BUILDER_ENCRYPTION_FILE_EXTENSION=.tet
PAGE_BUILDER_ENCRYPTION_REQUIRE_PASSWORD=true
```

### `config/page-builder.php`

```php
'encryption' => [
    'enabled'          => env('PAGE_BUILDER_ENCRYPTION_ENABLED', false),
    'key'              => env('PAGE_BUILDER_ENCRYPTION_KEY', ''),
    'file_extension'   => env('PAGE_BUILDER_ENCRYPTION_FILE_EXTENSION', '.tet'),
    'require_password' => env('PAGE_BUILDER_ENCRYPTION_REQUIRE_PASSWORD', true),
],
```

| Key | Purpose |
|---|---|
| `enabled` | When `true`, new exports are encrypted. When `false`, exports are plain JSON — but **encrypted files can still be imported** as long as the key is configured. |
| `key` | Base64‑encoded 32‑byte (256‑bit) key. Generate it once and store it in `.env`. |
| `file_extension` | Filename suffix applied to encrypted exports (default `.tet`). |
| `require_password` | When `true`, the UI prompts for an extra per‑export password on import; that password is used *in place of* the configured key. |

---

## Generating an encryption key

A valid key is 32 random bytes, base64‑encoded.

```php
use Trinavo\LivewirePageBuilder\Facades\ThemeEncryptionService;

$key = ThemeEncryptionService::generateEncryptionKey();
```

From the terminal:

```bash
php artisan tinker
>>> Trinavo\LivewirePageBuilder\Facades\ThemeEncryptionService::generateEncryptionKey();
```

Or without PHP:

```bash
openssl rand -base64 32
```

To check that an existing key has the right format:

```php
ThemeEncryptionService::validateEncryptionKey($key);   // bool
```

A key is valid when it base64‑decodes to exactly 32 bytes.

---

## Export & import behaviour

| Encryption setting | Export | Import |
|---|---|---|
| **Enabled** | New exports are AES‑256‑GCM encrypted, saved with the configured extension. | Both encrypted and plain JSON files are accepted. |
| **Disabled** | New exports are plain JSON, saved with `.json`. | Both encrypted and plain JSON files are accepted (the key must still be configured to decrypt). |

This asymmetry is intentional: turning encryption off for new exports (e.g. while debugging) shouldn't lock you out of old encrypted exports.

The editor's import dialog accepts any file type. Detection happens by inspecting the file contents for the `"encrypted": true` envelope marker, so there's no risk of selecting the wrong format.

---

## Programmatic use

### Default path — let `ThemeService` decide

```php
use Trinavo\LivewirePageBuilder\Facades\ThemeService;

$jsonOrCiphertext = ThemeService::exportThemeAsJson(1);   // encrypted if enabled
$theme            = ThemeService::importThemeFromFile($path);  // auto-detects
```

### Force encrypted IO regardless of setting

Useful when you have a global setting of "off" but want to encrypt this particular export:

```php
$encrypted = ThemeService::exportThemeAsEncryptedJson(1, password: 'extra-password');
$path      = ThemeService::exportThemeToEncryptedFile(1, directory: null, password: null);

$theme = ThemeService::importEncryptedTheme($encryptedString, overwriteExisting: false, password: null);
$theme = ThemeService::importThemeFromEncryptedFile($path, overwriteExisting: false, password: null);
```

When `password` is null the configured key is used; when a string is passed, **that string is used in place of the configured key** for this operation only.

### Directly using `ThemeEncryptionService`

```php
use Trinavo\LivewirePageBuilder\Facades\ThemeEncryptionService;

$envelope = ThemeEncryptionService::encryptThemeData($themeArray, password: null);   // returns the JSON envelope as a string
$data     = ThemeEncryptionService::decryptThemeData($envelopeJson, password: null); // returns the original array

ThemeEncryptionService::isEncrypted($jsonString);    // bool
```

### Runtime configuration overrides

Every config value has a setter on the service:

```php
ThemeEncryptionService::setEncryptionEnabled(true);
ThemeEncryptionService::setEncryptionKey($key);
ThemeEncryptionService::setFileExtension('.secure');
ThemeEncryptionService::setRequirePassword(false);

// Reset to whatever is in config/ENV
ThemeEncryptionService::flushCache();
```

Setters are useful in tests or multi‑tenant setups where the key changes per request.

---

## Service API reference

`ThemeEncryptionService` (also available as the `ThemeEncryptionService` facade):

| Method | Purpose |
|---|---|
| `isEncryptionEnabled(): bool` | Whether new exports will be encrypted |
| `isEncryptionConfigured(): bool` | Whether a key is present and decryption can succeed |
| `setEncryptionEnabled(bool): self` | Toggle encryption for new exports |
| `setEncryptionKey(string): self` / `getEncryptionKey(): ?string` | Override / read the active key |
| `setFileExtension(string): self` / `getFileExtension(): string` | Override / read the encrypted file extension |
| `setRequirePassword(bool): self` / `isPasswordRequired(): bool` | Override / read the password requirement |
| `encryptThemeData(array, ?string $password = null): ?string` | Produce the encrypted JSON envelope (returns `null` if encryption is disabled) |
| `decryptThemeData(string, ?string $password = null): ?array` | Parse and decrypt an envelope back to a theme array |
| `isEncrypted(string): bool` | Quick check on a string payload |
| `generateEncryptionKey(): string` | New 32‑byte key, base64‑encoded |
| `validateEncryptionKey(string): bool` | True when the key base64‑decodes to 32 bytes |
| `loadFromConfig(): self` / `flushCache(): self` | Sync the service's in‑memory cache with config |

---

## Operational guidance

- **Never commit `PAGE_BUILDER_ENCRYPTION_KEY`** to version control. Store it in your secrets manager (Vault, AWS Secrets Manager, Doppler, …).
- **Use different keys per environment.** A leaked staging key shouldn't put production at risk.
- **Back up your key.** Without it, encrypted exports become opaque blobs that cannot be recovered — the package keeps no recovery copy.
- **Rotate keys** by exporting under the old key, importing with the old key configured, then re‑exporting under the new key. Both keys can be in play if you orchestrate the migration through your application code (use `setEncryptionKey()` to swap at runtime).
- **Auditability.** Encryption metadata (`encrypted`, `version`, `encrypted_at`) is the only thing visible in an encrypted file — useful for sorting / inventorying without decrypting.

---

## Common errors

| Symptom | Likely cause |
|---|---|
| `"This file is encrypted but no encryption key is configured."` | `PAGE_BUILDER_ENCRYPTION_KEY` is empty. Set it and retry. |
| `"This file is encrypted but cannot be decrypted."` | Wrong key, file corruption, or modified ciphertext. |
| `"This file appears to be encrypted but has an invalid format."` | The envelope is malformed (truncated upload, wrong file). |
| Import silently fails on a `.tet` file | The configured key in this environment differs from the one used to encrypt. Same key, different installs is fine; rotated key is not. |

Catch `Trinavo\LivewirePageBuilder\Exceptions\ThemeEncryptionException` to handle these programmatically.

---

## See also

- [Theme Service Usage](theme-service-usage.md) — the `ThemeService` API that wraps this service
- [Advanced Configuration](advanced-configuration.md) — every `encryption.*` config key in context
