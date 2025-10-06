<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class PreviewRouteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function preview_cancel_route_clears_session_and_redirects(): void
    {
        $theme = Theme::create(['name' => 'Test Theme', 'description' => 'Test']);
        session(['page_builder_preview_theme_id' => $theme->id]);

        $this->assertEquals($theme->id, session('page_builder_preview_theme_id'));

        $response = $this->get('/page-builder/preview/cancel');

        $response->assertRedirect('/');
        $this->assertNull(session('page_builder_preview_theme_id'));
    }

    /** @test */
    public function preview_cancel_route_works_when_no_preview_active(): void
    {
        $this->assertNull(session('page_builder_preview_theme_id'));

        $response = $this->get('/page-builder/preview/cancel');

        $response->assertRedirect('/');
        $this->assertNull(session('page_builder_preview_theme_id'));
    }

    /** @test */
    public function preview_cancel_route_has_correct_name(): void
    {
        $url = route('page-builder.preview.cancel');

        $this->assertEquals(url('/page-builder/preview/cancel'), $url);
    }

    /** @test */
    public function preview_session_persists_across_requests(): void
    {
        $theme = Theme::create(['name' => 'Test Theme', 'description' => 'Test']);

        // Set preview in first request
        session(['page_builder_preview_theme_id' => $theme->id]);

        // Verify it persists
        $this->assertEquals($theme->id, session('page_builder_preview_theme_id'));

        // Make another request (simulating navigation)
        $response = $this->withSession(['page_builder_preview_theme_id' => $theme->id])
            ->get('/');

        $this->assertEquals($theme->id, session('page_builder_preview_theme_id'));
    }
}
