<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Illuminate\Support\Facades\File;

class IconService
{
    /**
     * Get all available icons from specified sets
     */
    public function getIcons(array $sets = ['heroicons']): array
    {
        $icons = [];

        foreach ($sets as $set) {
            if ($set === 'heroicons') {
                $icons['heroicons'] = $this->getHeroicons();
            } elseif ($set === 'bootstrap') {
                $icons['bootstrap'] = $this->getBootstrapIcons();
            }
        }

        return $icons;
    }

    /**
     * Get all available Heroicons grouped by style
     */
    public function getHeroicons(): array
    {
        return $this->scanHeroicons();
    }

    /**
     * Get all available Bootstrap Icons grouped by style
     */
    public function getBootstrapIcons(): array
    {
        return $this->scanBootstrapIcons();
    }

    /**
     * Search icons by name with keyword enhancement
     */
    public function searchIcons(string $query, ?string $style = null, array $sets = ['heroicons']): array
    {
        $query = strtolower(trim($query));
        $results = [];

        if (empty($query)) {
            return $results;
        }

        // Get keyword mappings for enhanced search
        $iconKeywords = app(IconKeywords::class);
        $keywordMatches = $iconKeywords->findIconsByKeyword(query: $query);

        // Build search patterns: original query + keyword matches
        $searchPatterns = array_merge([$query], $keywordMatches);

        // Get all icons from specified sets
        $allIconSets = $this->getIcons(sets: $sets);

        foreach ($allIconSets as $setName => $setIcons) {
            foreach ($setIcons as $styleName => $styleIcons) {
                if ($style && $style !== $styleName) {
                    continue;
                }

                $filtered = array_filter($styleIcons, function ($icon) use ($searchPatterns) {
                    $iconName = strtolower($icon['name']);
                    $searchTerms = strtolower($icon['searchTerms']);

                    // Check if icon matches any of the search patterns
                    foreach ($searchPatterns as $pattern) {
                        if (str_contains($iconName, $pattern) || str_contains($searchTerms, $pattern)) {
                            return true;
                        }
                    }

                    return false;
                });

                if (! empty($filtered)) {
                    $results[$setName][$styleName] = array_values($filtered);
                }
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
     * Scan Heroicons directory and build icon list
     */
    private function scanHeroicons(): array
    {
        // Try multiple possible paths for better compatibility with test environments
        $possiblePaths = [
            base_path('vendor/blade-ui-kit/blade-heroicons/resources/svg'),
            realpath(dirname(__DIR__, 2).'/../vendor/blade-ui-kit/blade-heroicons/resources/svg'),
        ];

        $heroiconsPath = null;
        foreach ($possiblePaths as $path) {
            if ($path && File::exists($path)) {
                $heroiconsPath = $path;
                break;
            }
        }

        if (! $heroiconsPath) {
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
        foreach (array_keys($icons) as $style) {
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

    /**
     * Scan Bootstrap Icons directory and build icon list
     */
    private function scanBootstrapIcons(): array
    {
        // Try multiple possible paths for better compatibility with test environments
        $possiblePaths = [
            base_path('vendor/davidhsianturi/blade-bootstrap-icons/resources/svg'),
            realpath(dirname(__DIR__, 2).'/../vendor/davidhsianturi/blade-bootstrap-icons/resources/svg'),
        ];

        $bootstrapIconsPath = null;
        foreach ($possiblePaths as $path) {
            if ($path && File::exists($path)) {
                $bootstrapIconsPath = $path;
                break;
            }
        }

        if (! $bootstrapIconsPath) {
            return [
                'regular' => [],
                'fill' => [],
            ];
        }

        $files = File::allFiles($bootstrapIconsPath);
        $icons = [
            'regular' => [],
            'fill' => [],
        ];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'svg') {
                continue;
            }

            $filename = $file->getFilenameWithoutExtension();

            // Determine style and icon name
            if (str_ends_with($filename, '-fill')) {
                $style = 'fill';
                $iconName = substr($filename, 0, -5); // Remove '-fill' suffix
            } else {
                $style = 'regular';
                $iconName = $filename;
            }

            $icons[$style][] = [
                'name' => $iconName,
                'filename' => $filename,
                'component' => "bi-{$filename}",
                'searchTerms' => str_replace('-', ' ', $iconName),
            ];
        }

        // Sort each style alphabetically by name
        foreach (array_keys($icons) as $style) {
            usort($icons[$style], fn ($a, $b) => strcmp($a['name'], $b['name']));
        }

        return $icons;
    }
}
