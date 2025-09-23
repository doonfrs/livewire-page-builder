<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class RowHeightPropertyRenderingTest extends TestCase
{
    /** @test */
    public function row_css_classes_include_height_properties(): void
    {
        $service = new PageBuilderService;

        $properties = [
            'flex' => 'row',
            'contentAlign' => 'content-center',
            'contentWidthMobile' => 'w-full',
            'contentWidthTablet' => 'w-full',
            'contentWidthDesktop' => 'w-full',
            'desktopHeight' => 'h-full',
        ];

        $cssClasses = $service->getRowCssClassesFromProperties($properties);

        $this->assertStringContainsString('@5xl:h-full', $cssClasses);
        $this->assertStringNotContainsString('h-full h-full', $cssClasses); // No duplication
    }

    /** @test */
    public function row_css_classes_include_different_height_properties(): void
    {
        $service = new PageBuilderService;

        $properties = [
            'flex' => 'col',
            'mobileHeight' => 'h-screen',
            'tabletHeight' => 'h-96',
            'desktopHeight' => 'h-full',
        ];

        $cssClasses = $service->getRowCssClassesFromProperties($properties);

        $this->assertStringContainsString('h-screen', $cssClasses);
        $this->assertStringContainsString('@3xl:h-96', $cssClasses);
        $this->assertStringContainsString('@5xl:h-full', $cssClasses);
    }

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
        $this->assertStringNotContainsString('@5xl:', $cssClasses); // No responsive height classes
    }

    /** @test */
    public function row_css_classes_include_min_height_properties(): void
    {
        $service = new PageBuilderService;

        $properties = [
            'flex' => 'col',
            'desktopHeight' => 'h-screen',
            'desktopMinHeight' => 'min-h-96',
        ];

        $cssClasses = $service->getRowCssClassesFromProperties($properties);

        $this->assertStringContainsString('@5xl:h-screen', $cssClasses);
        $this->assertStringContainsString('@5xl:min-h-96', $cssClasses);
    }

    /** @test */
    public function height_css_classes_method_handles_full_height_correctly(): void
    {
        $service = new PageBuilderService;

        $properties = [
            'desktopHeight' => 'h-full',
        ];

        $heightClasses = $service->getHeightCssClassesFromProperties($properties);

        $this->assertEquals('@5xl:h-full', $heightClasses);
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
        $this->assertStringContainsString('@5xl:h-[500px]', $heightClasses);
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
        $this->assertStringContainsString('@5xl:h-full', $heightClasses);
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

    /** @test */
    public function height_properties_work_in_integration_with_property_updates(): void
    {
        // This test simulates the user scenario: setting height to full on a deeply nested row
        $service = new PageBuilderService;

        // Initial properties (no height set)
        $initialProperties = [
            'flex' => 'row',
            'contentAlign' => 'content-center',
            'contentWidthMobile' => 'w-full',
            'contentWidthTablet' => 'w-full',
            'contentWidthDesktop' => 'w-full',
            'desktopHeight' => '',
        ];

        // Generate CSS classes before height update
        $initialCssClasses = $service->getRowCssClassesFromProperties($initialProperties);
        $this->assertStringContainsString('h-full', $initialCssClasses); // Fallback h-full

        // Simulate property update: set desktopHeight to 'h-full'
        $updatedProperties = array_merge($initialProperties, [
            'desktopHeight' => 'h-full',
        ]);

        // Generate CSS classes after height update
        $updatedCssClasses = $service->getRowCssClassesFromProperties($updatedProperties);
        $this->assertStringContainsString('@5xl:h-full', $updatedCssClasses);

        // Verify the height property is correctly processed
        $heightClasses = $service->getHeightCssClassesFromProperties($updatedProperties);
        $this->assertEquals('@5xl:h-full', $heightClasses);
    }
}