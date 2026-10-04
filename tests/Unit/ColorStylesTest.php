<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class ColorStylesTest extends TestCase
{
    #[Test]
    public function a_named_color_with_an_opacity_suffix_becomes_a_class_without_a_warning(): void
    {
        Log::spy();
        $service = new PageBuilderService;
        $properties = ['textColor' => 'base-content/70', 'backgroundColor' => 'base-200/50'];

        $classes = $service->getCssClassesFromProperties($properties);
        $styles = $service->getInlineStylesFromProperties($properties);

        $this->assertStringContainsString('text-base-content/70', $classes);
        $this->assertStringContainsString('bg-base-200/50', $classes);
        $this->assertSame('', $styles);
        Log::shouldNotHaveReceived('warning');
    }

    #[Test]
    public function a_hex_color_is_rendered_inline(): void
    {
        $service = new PageBuilderService;

        $styles = $service->getInlineStylesFromProperties(['textColor' => '#ff0000', 'backgroundColor' => 'rgb(0, 0, 0)']);

        $this->assertStringContainsString('color: #ff0000', $styles);
        $this->assertStringContainsString('background-color: rgb(0, 0, 0)', $styles);
    }

    #[Test]
    public function an_injected_inline_color_is_dropped_and_logged(): void
    {
        Log::spy();
        $service = new PageBuilderService;

        $styles = $service->getInlineStylesFromProperties([
            'textColor' => '#fff;background:url(https://evil.test)',
            'backgroundColor' => 'rgb(0,0,0);position:fixed',
        ]);

        $this->assertSame('', $styles);
        Log::shouldHaveReceived('warning')->twice();
    }
}
