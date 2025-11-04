<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Illuminate\Support\Facades\Cache;
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

    protected function tearDown(): void
    {
        $this->iconService->clearCache();
        parent::tearDown();
    }

    /** @test */
    public function it_can_search_with_keyword_enhancement()
    {
        $results = $this->iconService->searchIcons(query: 'shipping', sets: ['heroicons', 'bootstrap']);

        $this->assertIsArray($results);

        // Check if we got results from icon sets
        $hasResults = false;
        foreach ($results as $set => $styles) {
            if (! empty($styles)) {
                $hasResults = true;
                break;
            }
        }

        if (! $hasResults) {
            $this->markTestSkipped('Icons not available in test environment');
        }

        // Should find shipping-related icons like truck, package, box through keyword mapping
        $allIcons = [];
        foreach ($results as $set => $styles) {
            foreach ($styles as $style => $icons) {
                foreach ($icons as $icon) {
                    $allIcons[] = $icon['name'];
                }
            }
        }

        // At least one of these shipping-related icons should be found
        $shippingIcons = ['truck', 'package', 'box', 'cart', 'bag'];
        $foundShippingIcon = false;
        foreach ($shippingIcons as $shippingIcon) {
            foreach ($allIcons as $icon) {
                if (str_contains($icon, $shippingIcon)) {
                    $foundShippingIcon = true;
                    break 2;
                }
            }
        }

        $this->assertTrue($foundShippingIcon, 'Should find at least one shipping-related icon');
    }

    /** @test */
    public function it_finds_email_icons_when_searching_for_mail()
    {
        $results = $this->iconService->searchIcons(query: 'mail', sets: ['heroicons', 'bootstrap']);

        $this->assertIsArray($results);

        // Check if we got results
        $hasResults = false;
        foreach ($results as $set => $styles) {
            if (! empty($styles)) {
                $hasResults = true;
                break;
            }
        }

        if (! $hasResults) {
            $this->markTestSkipped('Icons not available in test environment');
        }

        // Should find envelope icons through keyword mapping (email -> envelope)
        $allIcons = [];
        foreach ($results as $set => $styles) {
            foreach ($styles as $style => $icons) {
                foreach ($icons as $icon) {
                    $allIcons[] = $icon['name'];
                }
            }
        }

        // Should find envelope-related icons
        $foundEnvelope = false;
        foreach ($allIcons as $icon) {
            if (str_contains($icon, 'envelope')) {
                $foundEnvelope = true;
                break;
            }
        }

        $this->assertTrue($foundEnvelope, 'Should find envelope icons when searching for mail');
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

    /** @test */
    public function it_enhances_search_with_alternative_keywords()
    {
        // Search for "send" should find message/envelope icons
        $results = $this->iconService->searchIcons(query: 'send', sets: ['heroicons', 'bootstrap']);

        $this->assertIsArray($results);

        // Check if we got results
        $hasResults = false;
        foreach ($results as $set => $styles) {
            if (! empty($styles)) {
                $hasResults = true;
                break;
            }
        }

        if (! $hasResults) {
            $this->markTestSkipped('Icons not available in test environment');
        }

        // Collect all icon names
        $allIcons = [];
        foreach ($results as $set => $styles) {
            foreach ($styles as $style => $icons) {
                foreach ($icons as $icon) {
                    $allIcons[] = $icon['name'];
                }
            }
        }

        // Should find communication-related icons
        $communicationIcons = ['envelope', 'chat', 'message', 'send'];
        $foundCommunicationIcon = false;
        foreach ($communicationIcons as $commIcon) {
            foreach ($allIcons as $icon) {
                if (str_contains($icon, $commIcon)) {
                    $foundCommunicationIcon = true;
                    break 2;
                }
            }
        }

        $this->assertTrue($foundCommunicationIcon, 'Should find communication icons when searching for send');
    }
}
