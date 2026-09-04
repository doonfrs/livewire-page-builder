<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit\Http\Livewire;

use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties\ColorPicker;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class ColorPickerTest extends TestCase
{
    #[Test]
    public function it_renders(): void
    {
        Livewire::test(ColorPicker::class, [
            'propertyName' => 'text_color',
            'propertyLabel' => 'Text Color',
            'currentValue' => 'secondary',
        ])
            ->assertStatus(200)
            ->assertSee('secondary');
    }

    /*
     * Both hosts clip an absolutely positioned popover - the builder sidebar is an
     * overflow-hidden aside, the live edit sheet a max-h-[40dvh] scroll container - so the
     * palette has to leave the panel and be positioned against the viewport instead.
     */
    #[Test]
    public function its_palette_is_teleported_and_positioned_against_the_viewport(): void
    {
        $html = Livewire::test(ColorPicker::class, [
            'propertyName' => 'text_color',
            'propertyLabel' => 'Text Color',
            'currentValue' => '',
        ])->html();

        $this->assertStringContainsString('x-teleport="body"', $html);
        $this->assertStringContainsString('positionPopover()', $html);
        $this->assertStringNotContainsString('absolute bottom-full', $html);
    }

    #[Test]
    public function it_repositions_the_palette_when_the_panel_scrolls_under_it(): void
    {
        $html = Livewire::test(ColorPicker::class, [
            'propertyName' => 'text_color',
            'propertyLabel' => 'Text Color',
            'currentValue' => '',
        ])->html();

        // Capture phase: scroll does not bubble, so a plain .window listener would miss the
        // properties panel's own scroll container.
        $this->assertStringContainsString('scroll.window.capture', $html);
        $this->assertStringContainsString('resize.window', $html);
    }

    #[Test]
    public function it_selects_a_theme_color(): void
    {
        Livewire::test(ColorPicker::class, [
            'propertyName' => 'text_color',
            'propertyLabel' => 'Text Color',
            'currentValue' => '',
        ])
            ->call('selectColor', 'primary')
            ->assertSet('currentValue', 'primary');
    }
}
