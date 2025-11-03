<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class IconService
{
    private const CACHE_KEY = 'livewire_page_builder_heroicons';

    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get all available Heroicons grouped by style
     */
    public function getHeroicons(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->scanHeroicons();
        });
    }

    /**
     * Search icons by name
     */
    public function searchIcons(string $query, ?string $style = null): array
    {
        $icons = $this->getHeroicons();
        $query = strtolower($query);
        $results = [];

        foreach ($icons as $styleName => $styleIcons) {
            if ($style && $style !== $styleName) {
                continue;
            }

            $filtered = array_filter($styleIcons, function ($icon) use ($query) {
                return str_contains(strtolower($icon['name']), $query) ||
                       str_contains(strtolower($icon['searchTerms']), $query);
            });

            if (! empty($filtered)) {
                $results[$styleName] = array_values($filtered);
            }
        }

        return $results;
    }

    /**
     * Get icon component name for rendering
     */
    public function getComponentName(string $iconName): string
    {
        return "heroicon-{$iconName}";
    }

    /**
     * Clear the icons cache
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Scan Heroicons directory and build icon list
     */
    private function scanHeroicons(): array
    {
        $heroiconsPath = base_path('vendor/blade-ui-kit/blade-heroicons/resources/svg');

        if (! File::exists($heroiconsPath)) {
            return [
                'outline' => [],
                'solid' => [],
                'mini' => [],
            ];
        }

        $files = File::allFiles($heroiconsPath);
        $icons = [
            'outline' => [],
            'solid' => [],
            'mini' => [],
        ];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'svg') {
                continue;
            }

            $filename = $file->getFilenameWithoutExtension();

            // Extract style prefix and icon name
            if (! preg_match('/^([osm])-(.+)$/', $filename, $matches)) {
                continue;
            }

            $stylePrefix = $matches[1];
            $iconName = $matches[2];

            $style = $this->getStyleName($stylePrefix);

            if (! isset($icons[$style])) {
                continue;
            }

            $icons[$style][] = [
                'name' => $iconName,
                'filename' => $filename,
                'component' => "heroicon-{$filename}",
                'searchTerms' => str_replace('-', ' ', $iconName),
            ];
        }

        // Sort each style alphabetically by name
        foreach ($icons as $style => $styleIcons) {
            usort($icons[$style], fn ($a, $b) => strcmp($a['name'], $b['name']));
        }

        return $icons;
    }

    /**
     * Convert style prefix to full name
     */
    private function getStyleName(string $prefix): string
    {
        return match ($prefix) {
            'o' => 'outline',
            's' => 'solid',
            'm' => 'mini',
            default => 'unknown',
        };
    }
}
