<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties\ColorPicker;
use Trinavo\LivewirePageBuilder\Http\Livewire\PageEditor;
use Trinavo\LivewirePageBuilder\Models\BuilderPage;
use Trinavo\LivewirePageBuilder\Models\Theme;
use Trinavo\LivewirePageBuilder\Services\PageBuilderUIService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

/**
 * The editor canvas stands in for the host's site, so it has to be painted with the
 * host's theme. Without one it falls back to daisyUI's stock palette and a block looks
 * nothing like it does live - which is the whole point of a preview.
 */
class PreviewThemeTest extends TestCase
{
    protected Theme $theme;

    protected function setUp(): void
    {
        parent::setUp();

        $this->theme = Theme::create(['name' => 'Preview Theme']);

        BuilderPage::create([
            'key' => 'test-page',
            'theme_id' => $this->theme->id,
            'components' => [],
        ]);
    }

    protected function editorHtml(): string
    {
        return Livewire::test(PageEditor::class)
            ->set('pageKey', 'test-page')
            ->set('themeId', $this->theme->id)
            ->html();
    }

    #[Test]
    public function a_host_that_registers_no_theme_gets_the_editor_it_had_before(): void
    {
        $html = $this->editorHtml();

        $this->assertStringContainsString('data-pb-theme', $html);
        $this->assertStringNotContainsString('data-theme=', $html);
        $this->assertStringNotContainsString('[data-pb-theme] {', $html);
    }

    #[Test]
    public function the_canvas_carries_the_theme_the_host_registered(): void
    {
        app(PageBuilderUIService::class)->setPreviewTheme(
            name: fn () => 'bloom-sense',
            css: fn () => '--color-primary: oklch(35% 0.05 300); --radius-field: 2rem;',
        );

        $html = $this->editorHtml();

        $this->assertStringContainsString('data-pb-theme data-theme="bloom-sense"', $html);
        $this->assertStringContainsString('[data-pb-theme] {', $html);
        $this->assertStringContainsString('--radius-field: 2rem;', $html);
    }

    /*
     * A built-in daisyUI theme has no declarations to inject - its name on the element is
     * the whole palette - so a name without CSS still has to reach the canvas.
     */
    #[Test]
    public function a_name_without_css_themes_the_canvas_without_a_style_rule(): void
    {
        app(PageBuilderUIService::class)->setPreviewTheme(name: 'cupcake');

        $html = $this->editorHtml();

        $this->assertStringContainsString('data-theme="cupcake"', $html);
        $this->assertStringNotContainsString('[data-pb-theme] {', $html);
    }

    /*
     * The declarations are echoed unescaped inside <style>, so a `</style>` in them would
     * end the stylesheet and hand the rest of the value to the HTML parser.
     */
    #[Test]
    public function host_css_cannot_close_the_style_element(): void
    {
        app(PageBuilderUIService::class)
            ->setPreviewTheme(css: '--color-primary: red;</style><script>alert(1)</script>');

        $this->assertStringNotContainsString('<', app(PageBuilderUIService::class)->getPreviewThemeCss());
        $this->assertStringNotContainsString('<script>alert(1)', $this->editorHtml());
    }

    #[Test]
    public function the_colour_swatches_show_the_hosts_palette_not_daisyuis(): void
    {
        app(PageBuilderUIService::class)->setPreviewTheme(name: 'bloom-sense');

        $component = Livewire::test(ColorPicker::class, [
            'propertyName' => 'text_color',
            'propertyLabel' => 'Text Color',
            'currentValue' => 'primary',
        ]);

        // The current-value chip in the panel, plus one grid per colour group in the popover.
        $expected = 1 + count($component->get('themeColors'));

        $this->assertSame(
            $expected,
            substr_count($component->html(), 'data-pb-theme data-theme="bloom-sense"'),
        );
    }

    #[Test]
    public function clearing_the_ui_service_drops_the_preview_theme(): void
    {
        $ui = app(PageBuilderUIService::class);
        $ui->setPreviewTheme(name: 'cupcake', css: '--color-primary: red;');

        $ui->clear();

        $this->assertSame('', $ui->getPreviewThemeName());
        $this->assertSame('', $ui->getPreviewThemeCss());
    }
}
