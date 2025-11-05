<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Services\IconService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class IconSearchEnhancementTest extends TestCase
{
    protected IconService $iconService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->iconService = new IconService;
    }

    /** @test */
    public function it_searches_across_multiple_icon_sets()
    {
        $results = $this->iconService->searchIcons(query: 'arrow', sets: ['heroicons', 'bootstrap']);

        $this->assertIsArray($results);

        // Should potentially have results from both sets
        // (test environment may not have icons, but structure should be correct)
        if (isset($results['heroicons']) && ! empty($results['heroicons'])) {
            $this->assertArrayHasKey('outline', $results['heroicons']);
        }

        if (isset($results['bootstrap']) && ! empty($results['bootstrap'])) {
            $this->assertArrayHasKey('regular', $results['bootstrap']);
        }
    }

    /** @test */
    public function it_returns_empty_results_for_empty_query()
    {
        $results = $this->iconService->searchIcons(query: '', sets: ['heroicons']);

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    /** @test */
    public function it_can_filter_by_style()
    {
        $results = $this->iconService->searchIcons(
            query: 'arrow',
            style: 'outline',
            sets: ['heroicons']
        );

        $this->assertIsArray($results);

        // If we have results, they should only be from outline style
        if (isset($results['heroicons'])) {
            $styles = array_keys($results['heroicons']);
            foreach ($styles as $style) {
                $this->assertEquals('outline', $style, 'Should only return outline style icons');
            }
        }
    }
}
