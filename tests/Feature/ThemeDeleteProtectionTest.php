<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Trinavo\LivewirePageBuilder\Http\Livewire\ThemeManager;
use Trinavo\LivewirePageBuilder\Models\Setting;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Services\PageBuilderUIService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

/**
 * Which themes the Theme Manager refuses to delete: the default theme always,
 * and any theme the host protects with protectThemes().
 */
class ThemeDeleteProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // The UI service is a singleton; never leak a hook into the next test.
        app(PageBuilderUIService::class)->clear();

        parent::tearDown();
    }

    #[Test]
    public function an_unprotected_theme_is_deleted_with_its_pages(): void
    {
        $theme = Theme::create(['name' => 'Spare']);
        $theme->pages()->create(['key' => 'home', 'components' => []]);

        Livewire::test(ThemeManager::class)
            ->call('openDeleteModal', $theme->id)
            ->assertSet('showDeleteModal', true)
            ->call('deleteTheme')
            ->assertDispatched('notify', type: 'success');

        $this->assertNull(Theme::find($theme->id));
        $this->assertSame(0, $theme->pages()->count());
    }

    #[Test]
    public function the_default_theme_is_refused_before_the_confirmation_opens(): void
    {
        $default = Theme::create(['name' => 'Live']);
        Setting::setDefaultThemeId($default->id);

        Livewire::test(ThemeManager::class)
            ->call('openDeleteModal', $default->id)
            ->assertSet('showDeleteModal', false)
            ->assertDispatched('notify', message: __('Cannot delete the default theme'));

        $this->assertNotNull(Theme::find($default->id));
    }

    #[Test]
    public function a_protected_theme_shows_the_host_message_instead_of_the_confirmation(): void
    {
        $kept = Theme::create(['name' => 'Fallback']);

        app(PageBuilderUIService::class)->protectThemes(
            isProtected: fn (Theme $theme) => $theme->id === $kept->id,
            message: fn (Theme $theme) => $theme->name.' stays',
        );

        Livewire::test(ThemeManager::class)
            ->call('openDeleteModal', $kept->id)
            ->assertSet('showDeleteModal', false)
            ->assertDispatched('notify', message: 'Fallback stays', type: 'warning');

        $this->assertNotNull(Theme::find($kept->id));
    }

    #[Test]
    public function a_protected_theme_is_refused_even_when_the_delete_arrives_directly(): void
    {
        $kept = Theme::create(['name' => 'Fallback']);
        $component = Livewire::test(ThemeManager::class)->call('openDeleteModal', $kept->id);

        // Protected after the confirmation opened: the delete itself re-checks.
        app(PageBuilderUIService::class)->protectThemes(fn (Theme $theme) => $theme->id === $kept->id);

        $component->call('deleteTheme')
            ->assertSet('showDeleteModal', false)
            ->assertDispatched('notify', message: __('This theme cannot be deleted'));

        $this->assertNotNull(Theme::find($kept->id));
    }

    #[Test]
    public function the_protection_closure_does_not_run_while_the_list_renders(): void
    {
        Theme::create(['name' => 'One']);
        Theme::create(['name' => 'Two']);
        $calls = 0;

        app(PageBuilderUIService::class)->protectThemes(function () use (&$calls) {
            $calls++;

            return true;
        });

        Livewire::test(ThemeManager::class)->assertStatus(200);

        $this->assertSame(0, $calls);
    }
}
