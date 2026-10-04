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
 * The two host hooks on the Theme Manager: allowThemeTransfer() for export and
 * import, lockThemes() for themes that may not be made the default. Both default
 * to today's behaviour, so a host that sets neither sees no change.
 */
class ThemeTransferAndLockTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // The UI service is a singleton; never leak a hook into the next test.
        app(PageBuilderUIService::class)->clear();

        parent::tearDown();
    }

    #[Test]
    public function export_and_import_are_offered_by_default(): void
    {
        $theme = Theme::create(['name' => 'Shop']);

        Livewire::test(ThemeManager::class)
            ->assertSee(__('Import Theme'))
            ->assertSee(__('Export'))
            ->call('exportTheme', $theme->id)
            ->assertFileDownloaded();
    }

    #[Test]
    public function a_host_that_forbids_transfer_hides_and_refuses_export_and_import(): void
    {
        $theme = Theme::create(['name' => 'Shop']);

        app(PageBuilderUIService::class)->allowThemeTransfer(fn () => false);

        Livewire::test(ThemeManager::class)
            ->assertDontSee(__('Import Theme'))
            ->assertDontSee(__('Export'))
            ->call('exportTheme', $theme->id)
            ->assertForbidden();

        Livewire::test(ThemeManager::class)
            ->call('openImportModal')
            ->assertForbidden();

        Livewire::test(ThemeManager::class)
            ->call('importTheme')
            ->assertForbidden();
    }

    #[Test]
    public function a_locked_theme_looks_like_any_other_theme(): void
    {
        $free = Theme::create(['name' => 'Free']);
        $paid = Theme::create(['name' => 'Paid']);
        Setting::setDefaultThemeId($free->id);

        $this->lockPaid();

        Livewire::test(ThemeManager::class)
            ->assertDontSeeText('Locked')
            ->assertDontSee('https://shop.test/unlock/'.$paid->id)
            ->assertSeeHtml('confirmSetDefaultTheme('.$paid->id.')');
    }

    #[Test]
    public function set_as_default_on_a_locked_theme_explains_why_and_links_to_its_unlock_page(): void
    {
        $free = Theme::create(['name' => 'Free']);
        $paid = Theme::create(['name' => 'Paid']);
        Setting::setDefaultThemeId($free->id);

        $this->lockPaid();

        Livewire::test(ThemeManager::class)
            ->call('confirmSetDefaultTheme', $paid->id)
            ->assertSet('showDefaultModal', false)
            ->assertSet('showLockedModal', true)
            ->assertSee('Paid is not on your plan')
            ->assertSee('Upgrade to put it live.')
            ->assertSee('Go upgrade')
            ->assertSeeHtml('https://shop.test/unlock/'.$paid->id)
            ->call('closeLockedModal')
            ->assertSet('showLockedModal', false);

        $this->assertSame($free->id, Setting::getDefaultThemeId());
    }

    #[Test]
    public function a_locked_theme_is_refused_even_past_the_confirmation(): void
    {
        $free = Theme::create(['name' => 'Free']);
        $paid = Theme::create(['name' => 'Paid']);
        Setting::setDefaultThemeId($free->id);

        // Unlocked when the confirmation opens, locked by the time it is
        // confirmed: the second request has to check again.
        $locked = false;
        app(PageBuilderUIService::class)->lockThemes(function () use (&$locked) {
            return $locked;
        });

        $component = Livewire::test(ThemeManager::class)
            ->call('confirmSetDefaultTheme', $paid->id)
            ->assertSet('showDefaultModal', true);

        $locked = true;

        $component->call('setDefaultTheme')
            ->assertSet('showDefaultModal', false)
            ->assertSet('showLockedModal', true);

        $this->assertSame($free->id, Setting::getDefaultThemeId());
    }

    #[Test]
    public function a_host_that_gives_no_wording_gets_a_generic_notice_without_a_button(): void
    {
        $paid = Theme::create(['name' => 'Paid']);

        app(PageBuilderUIService::class)->lockThemes(fn (Theme $theme) => true);

        Livewire::test(ThemeManager::class)
            ->call('confirmSetDefaultTheme', $paid->id)
            ->assertSet('showLockedModal', true)
            ->assertSet('lockedNotice.title', __('This theme cannot be set as default'))
            ->assertSet('lockedNotice.url', '')
            ->assertDontSee(__('Learn more'));
    }

    #[Test]
    public function an_unlocked_theme_is_still_made_the_default(): void
    {
        $free = Theme::create(['name' => 'Free']);
        $other = Theme::create(['name' => 'Other']);
        Setting::setDefaultThemeId($free->id);

        app(PageBuilderUIService::class)->lockThemes(fn (Theme $theme) => false);

        Livewire::test(ThemeManager::class)
            ->call('confirmSetDefaultTheme', $other->id)
            ->assertSet('showDefaultModal', true)
            ->call('setDefaultTheme');

        $this->assertSame($other->id, Setting::getDefaultThemeId());
    }

    private function lockPaid(): void
    {
        app(PageBuilderUIService::class)->lockThemes(
            isLocked: fn (Theme $theme) => $theme->name === 'Paid',
            unlockUrl: fn (Theme $theme) => 'https://shop.test/unlock/'.$theme->id,
            title: fn (Theme $theme) => $theme->name.' is not on your plan',
            message: 'Upgrade to put it live.',
            unlockLabel: 'Go upgrade',
        );
    }
}
