<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Trinavo\LivewirePageBuilder\Http\Livewire\LanguageSwitcher;
use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Http\Livewire\ThemeManager;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Setting;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Services\LocalizationService;
use Trinavo\LivewirePageBuilder\Services\PageBuilderUIService;
use Trinavo\LivewirePageBuilder\Services\ThemeService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

/**
 * The theme card's picture, the built-in Theme Settings field it comes from,
 * the Set as Default button outside the card's menu, and the editor's layout
 * guard.
 */
class ThemeCardAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    private const SHOT = 'https://cdn.test/volt/screenshots/preview.7b748a67.jpg';

    protected function tearDown(): void
    {
        // The UI service is a singleton; never leak a hook into the next test.
        app(PageBuilderUIService::class)->clear();

        parent::tearDown();
    }

    #[Test]
    public function a_theme_card_shows_its_preview_image(): void
    {
        $this->themeWithPicture('Volt');

        Livewire::test(ThemeManager::class)
            ->assertStatus(200)
            ->assertSeeHtml('src="'.self::SHOT.'"');
    }

    #[Test]
    public function the_host_can_change_the_displayed_image_without_touching_the_stored_one(): void
    {
        $theme = $this->themeWithPicture('Volt');

        app(PageBuilderUIService::class)
            ->themePreviewImageUsing(fn (string $url) => 'https://img.test/800x450/'.basename($url));

        Livewire::test(ThemeManager::class)
            ->assertSeeHtml('src="https://img.test/800x450/preview.7b748a67.jpg"')
            ->assertDontSeeHtml('src="'.self::SHOT.'"');

        $this->assertSame(self::SHOT, $theme->fresh()->previewImageUrl());
    }

    #[Test]
    public function a_theme_without_a_picture_shows_no_image(): void
    {
        Theme::create(['name' => 'Blank']);

        Livewire::test(ThemeManager::class)
            ->assertStatus(200)
            ->assertSee('Blank')
            ->assertDontSeeHtml('<img');
    }

    #[Test]
    public function set_as_default_is_a_card_button_on_every_theme_but_the_default(): void
    {
        $default = Theme::create(['name' => 'Live']);
        $other = Theme::create(['name' => 'Other']);
        Setting::setDefaultThemeId($default->id);

        Livewire::test(ThemeManager::class)
            ->assertSeeHtml('confirmSetDefaultTheme('.$other->id.')')
            ->assertDontSeeHtml('confirmSetDefaultTheme('.$default->id.')')
            ->assertSeeHtml('title="'.__('Default theme').'"')
            ->call('confirmSetDefaultTheme', $other->id)
            ->assertSet('showDefaultModal', true)
            ->call('setDefaultTheme');

        $this->assertSame($other->id, Setting::getDefaultThemeId());
    }

    #[Test]
    public function create_is_in_the_actions_menu_and_import_only_when_transfer_is_allowed(): void
    {
        Livewire::test(ThemeManager::class)
            ->assertSeeHtml('wire:click="openCreateModal"')
            ->assertSeeHtml('wire:click="openImportModal"');

        app(PageBuilderUIService::class)->allowThemeTransfer(fn () => false);

        Livewire::test(ThemeManager::class)
            ->assertSeeHtml('wire:click="openCreateModal"')
            ->assertDontSeeHtml('wire:click="openImportModal"');
    }

    #[Test]
    public function theme_settings_store_a_preview_image_url(): void
    {
        $theme = Theme::create(['name' => 'Shop']);

        $this->editor($theme)
            ->call('openThemeSettingsModal')
            ->set('themeSettingsForm.'.Theme::PREVIEW_IMAGE_SETTING, self::SHOT)
            ->call('saveThemeSettings')
            ->assertHasNoErrors();

        $this->assertSame(self::SHOT, $theme->fresh()->previewImageUrl());
    }

    #[Test]
    public function theme_settings_refuse_a_preview_image_that_is_not_a_web_address(): void
    {
        $theme = Theme::create(['name' => 'Shop']);

        $this->editor($theme)
            ->call('openThemeSettingsModal')
            ->set('themeSettingsForm.'.Theme::PREVIEW_IMAGE_SETTING, 'javascript:alert(1)')
            ->call('saveThemeSettings')
            ->assertHasErrors('themeSettingsForm.'.Theme::PREVIEW_IMAGE_SETTING);

        $this->assertNull($theme->fresh()->previewImageUrl());
    }

    #[Test]
    public function clearing_the_preview_image_removes_it_and_keeps_other_settings(): void
    {
        $theme = $this->themeWithPicture('Shop');
        $theme->setSetting('source_template', 'volt')->save();

        $this->editor($theme)
            ->call('openThemeSettingsModal')
            ->assertSet('themeSettingsForm.'.Theme::PREVIEW_IMAGE_SETTING, self::SHOT)
            ->set('themeSettingsForm.'.Theme::PREVIEW_IMAGE_SETTING, '')
            ->call('saveThemeSettings');

        $theme->refresh();
        $this->assertNull($theme->previewImageUrl());
        $this->assertSame('volt', $theme->getSetting('source_template'));
    }

    #[Test]
    public function the_preview_image_travels_with_an_exported_theme(): void
    {
        $theme = $this->themeWithPicture('Volt');
        $service = app(ThemeService::class);

        $export = $service->exportTheme($theme->id);
        $export['name'] = 'Volt imported';
        $imported = $service->importTheme($export);

        $this->assertSame(self::SHOT, $imported->previewImageUrl());
    }

    #[Test]
    public function a_layout_that_is_not_configured_is_never_applied(): void
    {
        $theme = Theme::create(['name' => 'Shop']);
        $file = tempnam(sys_get_temp_dir(), 'layout');
        file_put_contents($file, json_encode(['components' => [
            'row-1' => ['blocks' => [], 'properties' => []],
        ]]));

        config()->set('page-builder.layouts', []);
        $this->editor($theme)
            ->call('applyLayout', $file)
            ->assertSet('rows', []);

        config()->set('page-builder.layouts', [$file]);
        $this->editor($theme)
            ->call('applyLayout', $file)
            ->assertCount('rows', 1);

        unlink($file);
    }

    #[Test]
    public function the_language_switcher_shows_the_locale_code(): void
    {
        app(LocalizationService::class)->setUiLocales(['ar' => 'Arabic', 'en' => 'English']);
        session(['page_builder_locale' => 'ar']);

        Livewire::test(LanguageSwitcher::class)
            ->assertSeeHtml('<span>AR</span>')
            ->assertSee('English');
    }

    private function themeWithPicture(string $name): Theme
    {
        $theme = Theme::create(['name' => $name]);
        $theme->setSetting(Theme::PREVIEW_IMAGE_SETTING, self::SHOT)->save();

        return $theme;
    }

    private function editor(Theme $theme)
    {
        $page = BuilderPage::firstOrCreate(['key' => 'test-page', 'theme_id' => $theme->id], ['components' => []]);

        return Livewire::test(PageEditor::class)
            ->set('pageKey', 'test-page')
            ->set('themeId', $theme->id)
            ->set('page', $page)
            ->set('rows', []);
    }
}
