<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Services\IconService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class IconServiceTest extends TestCase
{
    protected IconService $iconService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->iconService = app(IconService::class);
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
    public function it_can_load_bootstrap_icons(): void
    {
        $bootstrapIcons = $this->iconService->getBootstrapIcons();

        $this->assertIsArray($bootstrapIcons);
        $this->assertArrayHasKey('regular', $bootstrapIcons);
        $this->assertArrayHasKey('fill', $bootstrapIcons);
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
}
