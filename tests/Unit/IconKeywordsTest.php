<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Trinavo\LivewirePageBuilder\Services\IconKeywords;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class IconKeywordsTest extends TestCase
{
    protected IconKeywords $iconKeywords;

    protected function setUp(): void
    {
        parent::setUp();
        $this->iconKeywords = new IconKeywords;
    }

    protected function tearDown(): void
    {
        Cache::forget('livewire_page_builder_icon_keywords');
        parent::tearDown();
    }

    /** @test */
    public function it_can_load_keyword_mappings()
    {
        $mappings = $this->iconKeywords->getKeywordMappings();

        $this->assertIsArray($mappings);
        $this->assertArrayHasKey('metadata', $mappings);
        $this->assertArrayHasKey('semantic_categories', $mappings);
    }

    /** @test */
    public function it_can_find_icons_by_keyword()
    {
        $results = $this->iconKeywords->findIconsByKeyword(query: 'shipping');

        $this->assertIsArray($results);
        // Should find shipping-related keywords like truck, package, box
        $this->assertNotEmpty($results);
    }

    /** @test */
    public function it_returns_empty_array_for_empty_query()
    {
        $results = $this->iconKeywords->findIconsByKeyword(query: '');

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    /** @test */
    public function it_can_search_in_semantic_categories()
    {
        $results = $this->iconKeywords->findIconsByKeyword(query: 'navigation');

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        // Should find navigation-related icons
        $this->assertContains('arrow', $results);
    }

    /** @test */
    public function it_can_search_in_alternative_terms()
    {
        $results = $this->iconKeywords->findIconsByKeyword(query: 'email');

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        // Email is an alternative term for envelope
        $this->assertContains('envelope', $results);
    }

    /** @test */
    public function it_can_get_all_categories()
    {
        $categories = $this->iconKeywords->getCategories();

        $this->assertIsArray($categories);
        $this->assertNotEmpty($categories);
        $this->assertContains('navigation', $categories);
        $this->assertContains('communication', $categories);
    }

    /** @test */
    public function it_can_get_popular_icons_for_category()
    {
        $popular = $this->iconKeywords->getPopularIcons(category: 'navigation');

        $this->assertIsArray($popular);
        $this->assertNotEmpty($popular);
        $this->assertContains('arrow-down', $popular);
        $this->assertContains('arrow-up', $popular);
    }

    /** @test */
    public function it_returns_empty_array_for_invalid_category()
    {
        $popular = $this->iconKeywords->getPopularIcons(category: 'nonexistent');

        $this->assertIsArray($popular);
        $this->assertEmpty($popular);
    }

    /** @test */
    public function it_caches_keyword_mappings()
    {
        // Clear cache first
        $this->iconKeywords->clearCache();

        // First call should load from file and cache
        $firstCall = $this->iconKeywords->getKeywordMappings();

        // Verify cache was set
        $this->assertTrue(Cache::has('livewire_page_builder_icon_keywords'));

        // Second call should use cache
        $secondCall = $this->iconKeywords->getKeywordMappings();

        $this->assertEquals($firstCall, $secondCall);
    }

    /** @test */
    public function it_returns_unique_keyword_matches()
    {
        $results = $this->iconKeywords->findIconsByKeyword(query: 'send');

        $this->assertIsArray($results);
        // Should return unique values even if multiple categories match
        $uniqueResults = array_unique($results);
        $this->assertEquals(count($uniqueResults), count($results));
    }
}
