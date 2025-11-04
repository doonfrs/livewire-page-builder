<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class IconKeywords
{
    private const CACHE_KEY = 'livewire_page_builder_icon_keywords';

    private const CACHE_TTL = 86400; // 24 hours

    /**
     * Get keyword mappings from JSON file
     */
    public function getKeywordMappings(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->loadKeywordMappings();
        });
    }

    /**
     * Find icon names that match a search query using keywords
     */
    public function findIconsByKeyword(string $query): array
    {
        $query = strtolower(trim($query));
        $mappings = $this->getKeywordMappings();

        if (empty($query)) {
            return [];
        }

        $matchedKeywords = [];

        // Search in semantic categories
        if (isset($mappings['semantic_categories'])) {
            foreach ($mappings['semantic_categories'] as $category => $data) {
                // Check category name
                if (str_contains($category, $query)) {
                    $matchedKeywords = array_merge($matchedKeywords, $data['keywords'] ?? []);
                }

                // Check keywords
                foreach ($data['keywords'] ?? [] as $keyword) {
                    if (str_contains($keyword, $query)) {
                        $matchedKeywords[] = $keyword;
                    }
                }

                // Check alternative search terms
                foreach ($data['alternative_search_terms'] ?? [] as $term) {
                    if (str_contains($term, $query)) {
                        $matchedKeywords = array_merge($matchedKeywords, $data['keywords'] ?? []);
                        break;
                    }
                }
            }
        }

        // Search in concept mappings
        if (isset($mappings['concept_mappings'])) {
            foreach ($mappings['concept_mappings'] as $concept => $iconNames) {
                if (str_contains($concept, $query)) {
                    $matchedKeywords = array_merge($matchedKeywords, $iconNames);
                }
            }
        }

        return array_unique($matchedKeywords);
    }

    /**
     * Get popular icons for a category
     */
    public function getPopularIcons(string $category): array
    {
        $mappings = $this->getKeywordMappings();

        if (isset($mappings['semantic_categories'][$category]['popular_icons'])) {
            return $mappings['semantic_categories'][$category]['popular_icons'];
        }

        return [];
    }

    /**
     * Get all semantic categories
     */
    public function getCategories(): array
    {
        $mappings = $this->getKeywordMappings();

        return array_keys($mappings['semantic_categories'] ?? []);
    }

    /**
     * Clear the keywords cache
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Load keyword mappings from JSON file
     */
    private function loadKeywordMappings(): array
    {
        $possiblePaths = [
            storage_path('app/icon_keywords.json'),
            dirname(__DIR__, 2) . '/storage/app/icon_keywords.json',
            base_path('storage/app/icon_keywords.json'),
        ];

        foreach ($possiblePaths as $path) {
            if (File::exists($path)) {
                $contents = File::get($path);

                return json_decode($contents, true) ?? [];
            }
        }

        // Return empty structure if file not found
        return [
            'metadata' => [],
            'semantic_categories' => [],
            'concept_mappings' => [],
        ];
    }
}
