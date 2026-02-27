<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class RowHeightPropertyRenderingTest extends TestCase
{
    /** @test */
    public function row_css_classes_fallback_to_h_full_when_no_height_properties(): void
    {
        $service = new PageBuilderService;

        $properties = [
            'flex' => 'row',
            'contentAlign' => 'content-center',
            'contentWidthMobile' => 'w-full',
            // No height properties set
        ];

        $cssClasses = $service->getRowCssClassesFromProperties($properties);

        $this->assertStringContainsString('h-full', $cssClasses);
        $this->assertStringNotContainsString('@7xl:', $cssClasses); // No responsive height classes
    }

    /** @test */
    public function height_css_classes_method_handles_full_height_correctly(): void
    {
        $service = new PageBuilderService;

        $properties = [
            'desktopHeight' => 'h-full',
        ];

        $heightClasses = $service->getHeightCssClassesFromProperties($properties);

        $this->assertEquals('@7xl:h-full', $heightClasses);
    }

    /** @test */
    public function height_css_classes_method_handles_custom_arbitrary_values(): void
    {
        $service = new PageBuilderService;

        $properties = [
            'mobileHeight' => '300',
            'tabletHeight' => '400',
            'desktopHeight' => '500',
        ];

        $heightClasses = $service->getHeightCssClassesFromProperties($properties);

        $this->assertStringContainsString('h-[300px]', $heightClasses);
        $this->assertStringContainsString('@3xl:h-[400px]', $heightClasses);
        $this->assertStringContainsString('@7xl:h-[500px]', $heightClasses);
    }

    /** @test */
    public function height_css_classes_method_handles_mixed_values(): void
    {
        $service = new PageBuilderService;

        $properties = [
            'mobileHeight' => 'h-64',
            'tabletHeight' => '400',
            'desktopHeight' => 'h-full',
            'mobileMinHeight' => 'min-h-screen',
        ];

        $heightClasses = $service->getHeightCssClassesFromProperties($properties);

        $this->assertStringContainsString('h-64', $heightClasses);
        $this->assertStringContainsString('@3xl:h-[400px]', $heightClasses);
        $this->assertStringContainsString('@7xl:h-full', $heightClasses);
        $this->assertStringContainsString('min-h-screen', $heightClasses);
    }

    /** @test */
    public function height_css_classes_method_returns_empty_when_no_height_properties(): void
    {
        $service = new PageBuilderService;

        $properties = [
            'flex' => 'row',
            'backgroundColor' => '#ffffff',
            // No height properties
        ];

        $heightClasses = $service->getHeightCssClassesFromProperties($properties);

        $this->assertEquals('', $heightClasses);
    }
}
