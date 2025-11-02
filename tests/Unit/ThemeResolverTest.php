<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
