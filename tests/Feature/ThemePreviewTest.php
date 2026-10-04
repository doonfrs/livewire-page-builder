<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Trinavo\LivewirePageBuilder\Http\Livewire\PreviewBar;
use Trinavo\LivewirePageBuilder\Http\Livewire\ThemeManager;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class ThemePreviewTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_set_preview_theme_via_theme_manager(): void
    {
        $theme = Theme::create([
            'name' => 'Test Theme',
            'description' => 'Test Description',
        ]);

        Livewire::test(ThemeManager::class)
            ->call('previewTheme', $theme->id)
            ->assertRedirect('/');

        $this->assertEquals($theme->id, session('page_builder_preview_theme_id'));
    }

    #[Test]
    public function exit_preview_returns_to_the_theme_manager_it_was_started_from(): void
    {
        $theme = Theme::create(['name' => 'Test Theme']);

        Livewire::test(ThemeManager::class)->call('previewTheme', $theme->id);

        Livewire::test(PreviewBar::class)
            ->assertDontSee('Back to Themes')
            ->call('cancelPreview')
            ->assertRedirect(route('page-builder.themes'));

        $this->assertNull(session('page_builder_preview_theme_id'));
        $this->assertNull(session(PreviewBar::RETURN_URL_SESSION_KEY));
    }

    #[Test]
    public function exit_preview_goes_home_when_nothing_said_where_it_started(): void
    {
        $theme = Theme::create(['name' => 'Test Theme']);
        session(['page_builder_preview_theme_id' => $theme->id]);

        Livewire::test(PreviewBar::class)
            ->call('cancelPreview')
            ->assertRedirect('/');
    }

    /** @test */
    public function it_dispatches_notification_when_preview_starts(): void
    {
        $theme = Theme::create([
            'name' => 'Preview Theme',
            'description' => 'Test',
        ]);

        Livewire::test(ThemeManager::class)
            ->call('previewTheme', $theme->id)
            ->assertDispatched('notify');
    }

    /** @test */
    public function it_shows_error_when_previewing_non_existent_theme(): void
    {
        Livewire::test(ThemeManager::class)
            ->call('previewTheme', 99999)
            ->assertDispatched('notify');

        $this->assertNull(session('page_builder_preview_theme_id'));
    }

    /** @test */
    public function it_can_cancel_preview_mode(): void
    {
        $theme = Theme::create(['name' => 'Test Theme', 'description' => 'Test']);
        session(['page_builder_preview_theme_id' => $theme->id]);

        Livewire::test(ThemeManager::class)
            ->call('cancelPreview')
            ->assertRedirect('/page-builder/themes');

        $this->assertNull(session('page_builder_preview_theme_id'));
    }

    /** @test */
    public function it_dispatches_notification_when_preview_cancelled(): void
    {
        $theme = Theme::create(['name' => 'Test Theme', 'description' => 'Test']);
        session(['page_builder_preview_theme_id' => $theme->id]);

        Livewire::test(ThemeManager::class)
            ->call('cancelPreview')
            ->assertDispatched('notify');
    }

    /** @test */
    public function cancelling_preview_when_not_in_preview_mode_does_not_fail(): void
    {
        Livewire::test(ThemeManager::class)
            ->call('cancelPreview')
            ->assertRedirect('/page-builder/themes')
            ->assertDispatched('notify');

        $this->assertNull(session('page_builder_preview_theme_id'));
    }
}
