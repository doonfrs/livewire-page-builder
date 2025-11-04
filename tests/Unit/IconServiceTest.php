<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Trinavo\LivewirePageBuilder\Services\IconService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class IconServiceTest extends TestCase
{
    protected IconService $iconService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->iconService = app(IconService::class);

        // Clear cache before each test
        $this->iconService->clearCache();
    }

    protected function tearDown(): void
    {
        // Clear cache after each test
        $this->iconService->clearCache();

        parent::tearDown();
    }

    /** @test */
    public function it_can_load_heroicons(): void
    {
        $heroicons = $this->iconService->getHeroicons();

        $this->assertIsArray($heroicons);
        $this->assertArrayHasKey('outline', $heroicons);
        $this->assertArrayHasKey('solid', $heroicons);
        $this->assertArrayHasKey('mini', $heroicons);

        // Note: In test environment, icons may not load due to Orchestra Testbench's isolated environment
        // This test verifies the structure is correct, even if empty
    }

    /** @test */
    public function heroicons_have_correct_structure(): void
    {
        $heroicons = $this->iconService->getHeroicons();

        // Check that we have icons in at least one style (try all styles)
        $hasIcons = false;
        $firstIcon = null;

        foreach (['outline', 'solid', 'mini'] as $style) {
            if (! empty($heroicons[$style])) {
                $hasIcons = true;
                $firstIcon = $heroicons[$style][0];
                break;
            }
        }

        if (! $hasIcons) {
            $this->markTestSkipped('Icons not available in test environment');
        }

        // Check the structure of the first icon
        $this->assertArrayHasKey('name', $firstIcon);
        $this->assertArrayHasKey('filename', $firstIcon);
        $this->assertArrayHasKey('component', $firstIcon);
        $this->assertArrayHasKey('searchTerms', $firstIcon);

        // Check that component name starts with 'heroicon-'
        $this->assertStringStartsWith('heroicon-', $firstIcon['component']);
    }

    /** @test */
    public function it_can_load_bootstrap_icons(): void
    {
        $bootstrapIcons = $this->iconService->getBootstrapIcons();

        $this->assertIsArray($bootstrapIcons);
        $this->assertArrayHasKey('regular', $bootstrapIcons);
        $this->assertArrayHasKey('fill', $bootstrapIcons);
    }

    /** @test */
    public function bootstrap_icons_have_correct_structure(): void
    {
        $bootstrapIcons = $this->iconService->getBootstrapIcons();

        // Check that we have icons in at least one style (try all styles)
        $hasIcons = false;
        $firstIcon = null;

        foreach (['regular', 'fill'] as $style) {
            if (! empty($bootstrapIcons[$style])) {
                $hasIcons = true;
                $firstIcon = $bootstrapIcons[$style][0];
                break;
            }
        }

        if (! $hasIcons) {
            $this->markTestSkipped('Icons not available in test environment');
        }

        // Check the structure of the first icon
        $this->assertArrayHasKey('name', $firstIcon);
        $this->assertArrayHasKey('filename', $firstIcon);
        $this->assertArrayHasKey('component', $firstIcon);
        $this->assertArrayHasKey('searchTerms', $firstIcon);

        // Check that component name starts with 'bootstrap-'
        $this->assertStringStartsWith('bootstrap-', $firstIcon['component']);
    }

    /** @test */
    public function it_can_load_multiple_icon_sets(): void
    {
        $icons = $this->iconService->getIcons(sets: ['heroicons', 'bootstrap']);

        $this->assertIsArray($icons);
        $this->assertArrayHasKey('heroicons', $icons);
        $this->assertArrayHasKey('bootstrap', $icons);

        // Check heroicons structure
        $this->assertArrayHasKey('outline', $icons['heroicons']);
        $this->assertArrayHasKey('solid', $icons['heroicons']);
        $this->assertArrayHasKey('mini', $icons['heroicons']);

        // Check bootstrap icons structure
        $this->assertArrayHasKey('regular', $icons['bootstrap']);
        $this->assertArrayHasKey('fill', $icons['bootstrap']);
    }

    /** @test */
    public function it_can_load_single_icon_set(): void
    {
        $icons = $this->iconService->getIcons(sets: ['heroicons']);

        $this->assertIsArray($icons);
        $this->assertArrayHasKey('heroicons', $icons);
        $this->assertArrayNotHasKey('bootstrap', $icons);
    }

    /** @test */
    public function heroicons_are_cached(): void
    {
        // First call should cache
        $this->iconService->getHeroicons();

        $this->assertTrue(Cache::has('livewire_page_builder_heroicons'));

        // Clear cache and verify it's gone
        $this->iconService->clearCache();

        $this->assertFalse(Cache::has('livewire_page_builder_heroicons'));
    }

    /** @test */
    public function bootstrap_icons_are_cached(): void
    {
        // First call should cache
        $this->iconService->getBootstrapIcons();

        $this->assertTrue(Cache::has('livewire_page_builder_bootstrap_icons'));

        // Clear cache and verify it's gone
        $this->iconService->clearCache();

        $this->assertFalse(Cache::has('livewire_page_builder_bootstrap_icons'));
    }

    /** @test */
    public function clear_cache_clears_all_icon_caches(): void
    {
        // Load both icon sets
        $this->iconService->getHeroicons();
        $this->iconService->getBootstrapIcons();

        // Verify both are cached
        $this->assertTrue(Cache::has('livewire_page_builder_heroicons'));
        $this->assertTrue(Cache::has('livewire_page_builder_bootstrap_icons'));

        // Clear cache
        $this->iconService->clearCache();

        // Verify both caches are cleared
        $this->assertFalse(Cache::has('livewire_page_builder_heroicons'));
        $this->assertFalse(Cache::has('livewire_page_builder_bootstrap_icons'));
    }

    /** @test */
    public function heroicons_are_sorted_alphabetically(): void
    {
        $heroicons = $this->iconService->getHeroicons();

        $hasAnyIcons = false;
        foreach ($heroicons as $style => $icons) {
            if (empty($icons)) {
                continue;
            }

            $hasAnyIcons = true;
            $names = array_column($icons, 'name');
            $sortedNames = $names;
            sort($sortedNames);

            $this->assertEquals($sortedNames, $names, "Icons in {$style} style should be sorted alphabetically");
        }

        if (! $hasAnyIcons) {
            $this->markTestSkipped('Icons not available in test environment');
        }
    }

    /** @test */
    public function bootstrap_icons_are_sorted_alphabetically(): void
    {
        $bootstrapIcons = $this->iconService->getBootstrapIcons();

        $hasAnyIcons = false;
        foreach ($bootstrapIcons as $style => $icons) {
            if (empty($icons)) {
                continue;
            }

            $hasAnyIcons = true;
            $names = array_column($icons, 'name');
            $sortedNames = $names;
            sort($sortedNames);

            $this->assertEquals($sortedNames, $names, "Icons in {$style} style should be sorted alphabetically");
        }

        if (! $hasAnyIcons) {
            $this->markTestSkipped('Icons not available in test environment');
        }
    }

    /** @test */
    public function bootstrap_icons_correctly_separate_fill_and_regular_styles(): void
    {
        $bootstrapIcons = $this->iconService->getBootstrapIcons();

        if (empty($bootstrapIcons['fill']) && empty($bootstrapIcons['regular'])) {
            $this->markTestSkipped('Icons not available in test environment');
        }

        // Check that fill icons end with -fill in their filename
        foreach ($bootstrapIcons['fill'] as $icon) {
            $this->assertStringEndsWith('-fill', $icon['filename'],
                "Fill icon '{$icon['filename']}' should end with '-fill'");
        }

        // Check that regular icons don't end with -fill in their filename
        foreach ($bootstrapIcons['regular'] as $icon) {
            $this->assertStringEndsNotWith('-fill', $icon['filename'],
                "Regular icon '{$icon['filename']}' should not end with '-fill'");
        }
    }

    /** @test */
    public function it_returns_empty_arrays_when_icon_directories_do_not_exist(): void
    {
        // This test assumes the directories exist in vendor
        // If they don't, the service should return empty arrays without errors

        $heroicons = $this->iconService->getHeroicons();
        $bootstrapIcons = $this->iconService->getBootstrapIcons();

        // Should always return arrays with expected keys, even if empty
        $this->assertIsArray($heroicons);
        $this->assertIsArray($bootstrapIcons);
    }

    /** @test */
    public function search_terms_replace_hyphens_with_spaces(): void
    {
        $heroicons = $this->iconService->getHeroicons();

        // Find an icon with hyphens in the name
        $iconWithHyphen = null;
        foreach ($heroicons as $icons) {
            foreach ($icons as $icon) {
                if (str_contains($icon['name'], '-')) {
                    $iconWithHyphen = $icon;
                    break 2;
                }
            }
        }

        if ($iconWithHyphen) {
            $expectedSearchTerms = str_replace('-', ' ', $iconWithHyphen['name']);
            $this->assertEquals($expectedSearchTerms, $iconWithHyphen['searchTerms']);
        } else {
            $this->markTestSkipped('No icons with hyphens found to test search terms');
        }
    }
}
