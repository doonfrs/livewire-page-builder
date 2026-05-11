<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Trinavo\LivewirePageBuilder\Exceptions\InvalidThemeFormatException;
use Trinavo\LivewirePageBuilder\Exceptions\ThemeEncryptionException;
use Trinavo\LivewirePageBuilder\Exceptions\ThemeFileException;
use Trinavo\LivewirePageBuilder\Services\ThemeService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class ThemeImportErrorsTest extends TestCase
{
    use RefreshDatabase;

    private function tempFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'theme_');
        file_put_contents($path, $contents);

        return $path;
    }

    #[Test]
    public function missing_file_throws_theme_file_exception(): void
    {
        $service = app(ThemeService::class);

        $this->expectException(ThemeFileException::class);

        $service->importThemeFromFile('/nonexistent/path/theme.json');
    }

    #[Test]
    public function invalid_json_throws_invalid_theme_format_exception(): void
    {
        $service = app(ThemeService::class);
        $path = $this->tempFile('{ this is not json');

        try {
            $this->expectException(InvalidThemeFormatException::class);
            $service->importThemeFromFile($path);
        } finally {
            @unlink($path);
        }
    }

    #[Test]
    public function missing_required_fields_throws_invalid_theme_format_exception(): void
    {
        $service = app(ThemeService::class);
        $path = $this->tempFile(json_encode(['description' => 'no name, no pages']));

        try {
            $this->expectException(InvalidThemeFormatException::class);
            $service->importThemeFromFile($path);
        } finally {
            @unlink($path);
        }
    }

    #[Test]
    public function invalid_pages_format_throws_invalid_theme_format_exception(): void
    {
        $service = app(ThemeService::class);
        $path = $this->tempFile(json_encode([
            'name' => 'Bad Pages',
            'pages' => 'this should be an array',
        ]));

        try {
            $this->expectException(InvalidThemeFormatException::class);
            $service->importThemeFromFile($path);
        } finally {
            @unlink($path);
        }
    }

    #[Test]
    public function page_missing_key_throws_invalid_theme_format_exception(): void
    {
        $service = app(ThemeService::class);
        $path = $this->tempFile(json_encode([
            'name' => 'Missing Page Key',
            'pages' => [
                ['components' => []],
            ],
        ]));

        try {
            $this->expectException(InvalidThemeFormatException::class);
            $service->importThemeFromFile($path);
        } finally {
            @unlink($path);
        }
    }

    #[Test]
    public function encrypted_file_with_no_key_throws_theme_encryption_exception(): void
    {
        config()->set('page-builder.encryption.key', '');

        $service = app(ThemeService::class);
        $path = $this->tempFile(json_encode([
            'encrypted' => true,
            'version' => '1.0',
            'data' => 'irrelevant-without-a-key',
        ]));

        try {
            $this->expectException(ThemeEncryptionException::class);
            $service->importThemeFromFile($path);
        } finally {
            @unlink($path);
        }
    }
}
