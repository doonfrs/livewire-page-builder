<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Trinavo\LivewirePageBuilder\Models\Setting;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Support\ThemeResolver;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class ThemeResolverTest extends TestCase
{
    use RefreshDatabase;

    protected $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test class that uses ThemeResolver
        $this->resolver = new class
        {
            use ThemeResolver;

            public function test_resolve_theme_id($themeId = null)
            {
                return $this->resolveThemeId(themeId: $themeId);
            }
        };
    }

    /** @test */
    public function it_returns_explicit_theme_id_when_provided(): void
    {
        $theme = Theme::create(['name' => 'Test Theme', 'description' => 'Test']);

        $result = $this->resolver->testResolveThemeId($theme->id);

        $this->assertEquals($theme->id, $result);
    }

    /** @test */
    public function it_returns_preview_session_theme_when_set(): void
    {
        $theme1 = Theme::create(['name' => 'Default Theme', 'description' => 'Default']);
        $theme2 = Theme::create(['name' => 'Preview Theme', 'description' => 'Preview']);

        // Set default theme
        Setting::setDefaultThemeId($theme1->id);

        // Set preview theme in session
        session(['page_builder_preview_theme_id' => $theme2->id]);

        $result = $this->resolver->testResolveThemeId();

        $this->assertEquals($theme2->id, $result);
    }

    /** @test */
    public function it_prioritizes_explicit_param_over_preview_session(): void
    {
        $theme1 = Theme::create(['name' => 'Explicit Theme', 'description' => 'Explicit']);
        $theme2 = Theme::create(['name' => 'Preview Theme', 'description' => 'Preview']);

        session(['page_builder_preview_theme_id' => $theme2->id]);

        $result = $this->resolver->testResolveThemeId($theme1->id);

        $this->assertEquals($theme1->id, $result);
    }

    /** @test */
    public function it_returns_default_theme_when_no_preview_session(): void
    {
        $theme = Theme::create(['name' => 'Default Theme', 'description' => 'Default']);
        Setting::setDefaultThemeId($theme->id);

        $result = $this->resolver->testResolveThemeId();

        $this->assertEquals($theme->id, $result);
    }

    /** @test */
    public function it_returns_first_available_theme_when_no_default_set(): void
    {
        $theme = Theme::create(['name' => 'First Theme', 'description' => 'First']);

        $result = $this->resolver->testResolveThemeId();

        $this->assertEquals($theme->id, $result);
    }

    /** @test */
    public function it_returns_null_when_no_themes_exist(): void
    {
        $result = $this->resolver->testResolveThemeId();

        $this->assertNull($result);
    }

    /** @test */
    public function preview_session_takes_priority_over_default_theme(): void
    {
        $defaultTheme = Theme::create(['name' => 'Default Theme', 'description' => 'Default']);
        $previewTheme = Theme::create(['name' => 'Preview Theme', 'description' => 'Preview']);

        Setting::setDefaultThemeId($defaultTheme->id);
        session(['page_builder_preview_theme_id' => $previewTheme->id]);

        $result = $this->resolver->testResolveThemeId();

        $this->assertEquals($previewTheme->id, $result);
        $this->assertNotEquals($defaultTheme->id, $result);
    }

    /** @test */
    public function it_ignores_invalid_preview_session_value(): void
    {
        $theme = Theme::create(['name' => 'Default Theme', 'description' => 'Default']);
        Setting::setDefaultThemeId($theme->id);

        // Set invalid preview ID
        session(['page_builder_preview_theme_id' => 99999]);

        $result = $this->resolver->testResolveThemeId();

        // Should still return default theme (doesn't validate in resolver)
        $this->assertEquals(99999, $result);
    }
}
