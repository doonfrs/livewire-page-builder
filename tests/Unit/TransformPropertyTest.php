<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class TransformPropertyTest extends TestCase
{
    private PageBuilderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PageBuilderService::class);
    }

    /** @test */
    public function it_generates_rotate_class_for_common_angles(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileRotate' => 45,
            'tabletRotate' => 45,
            'desktopRotate' => 45,
        ]);

        $this->assertStringContainsString('rotate-45', $classes);
    }

    /** @test */
    public function it_generates_negative_rotate_class(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileRotate' => -90,
            'tabletRotate' => -90,
            'desktopRotate' => -90,
        ]);

        $this->assertStringContainsString('-rotate-90', $classes);
    }

    /** @test */
    public function it_generates_arbitrary_rotate_class_for_custom_angles(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileRotate' => 37,
            'tabletRotate' => 37,
            'desktopRotate' => 37,
        ]);

        $this->assertStringContainsString('rotate-[37deg]', $classes);
    }

    /** @test */
    public function it_generates_negative_arbitrary_rotate_class(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileRotate' => -37,
            'tabletRotate' => -37,
            'desktopRotate' => -37,
        ]);

        $this->assertStringContainsString('-rotate-[37deg]', $classes);
    }

    /** @test */
    public function it_generates_scale_class_for_common_values(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileScale' => 1.5,
            'tabletScale' => 1.5,
            'desktopScale' => 1.5,
        ]);

        $this->assertStringContainsString('scale-150', $classes);
    }

    /** @test */
    public function it_generates_arbitrary_scale_class_for_custom_values(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileScale' => 1.23,
            'tabletScale' => 1.23,
            'desktopScale' => 1.23,
        ]);

        $this->assertStringContainsString('scale-[123]', $classes);
    }

    /** @test */
    public function it_generates_translate_x_class_for_common_values(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileTranslateX' => 10,
            'tabletTranslateX' => 10,
            'desktopTranslateX' => 10,
        ]);

        $this->assertStringContainsString('translate-x-10', $classes);
    }

    /** @test */
    public function it_generates_negative_translate_x_class(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileTranslateX' => -20,
            'tabletTranslateX' => -20,
            'desktopTranslateX' => -20,
        ]);

        $this->assertStringContainsString('-translate-x-20', $classes);
    }

    /** @test */
    public function it_generates_arbitrary_translate_x_class_for_custom_values(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileTranslateX' => 15,
            'tabletTranslateX' => 15,
            'desktopTranslateX' => 15,
        ]);

        $this->assertStringContainsString('translate-x-[15px]', $classes);
    }

    /** @test */
    public function it_generates_translate_y_class_for_common_values(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileTranslateY' => 8,
            'tabletTranslateY' => 8,
            'desktopTranslateY' => 8,
        ]);

        $this->assertStringContainsString('translate-y-8', $classes);
    }

    /** @test */
    public function it_generates_skew_x_class_for_common_angles(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileSkewX' => 12,
            'tabletSkewX' => 12,
            'desktopSkewX' => 12,
        ]);

        $this->assertStringContainsString('skew-x-12', $classes);
    }

    /** @test */
    public function it_generates_arbitrary_skew_x_class_for_custom_angles(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileSkewX' => 15,
            'tabletSkewX' => 15,
            'desktopSkewX' => 15,
        ]);

        $this->assertStringContainsString('skew-x-[15deg]', $classes);
    }

    /** @test */
    public function it_generates_skew_y_class_for_common_angles(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileSkewY' => 6,
            'tabletSkewY' => 6,
            'desktopSkewY' => 6,
        ]);

        $this->assertStringContainsString('skew-y-6', $classes);
    }

    /** @test */
    public function it_does_not_generate_rotate_class_when_zero(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileRotate' => 0,
            'tabletRotate' => 0,
            'desktopRotate' => 0,
        ]);

        $this->assertStringNotContainsString('rotate', $classes);
    }

    /** @test */
    public function it_does_not_generate_scale_class_when_default(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileScale' => 1,
            'tabletScale' => 1,
            'desktopScale' => 1,
        ]);

        $this->assertStringNotContainsString('scale', $classes);
    }

    /** @test */
    public function it_generates_responsive_rotate_classes_with_container_queries(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileRotate' => 0,
            'tabletRotate' => 45,
            'desktopRotate' => 90,
        ]);

        $this->assertStringContainsString('@3xl:rotate-45', $classes);
        $this->assertStringContainsString('@5xl:rotate-90', $classes);
    }

    /** @test */
    public function it_generates_responsive_scale_classes(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileScale' => 1,
            'tabletScale' => 1.1,
            'desktopScale' => 1.5,
        ]);

        // 1.1 = 110 which is a common scale in Tailwind but might be arbitrary
        $this->assertStringContainsString('@3xl:scale-', $classes);
        $this->assertStringContainsString('110', $classes);
        $this->assertStringContainsString('@5xl:scale-150', $classes);
    }

    /** @test */
    public function it_generates_responsive_translate_x_classes(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileTranslateX' => 0,
            'tabletTranslateX' => 10,
            'desktopTranslateX' => 20,
        ]);

        $this->assertStringContainsString('@3xl:translate-x-10', $classes);
        $this->assertStringContainsString('@5xl:translate-x-20', $classes);
    }

    /** @test */
    public function it_generates_responsive_translate_y_classes(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileTranslateY' => 5,
            'tabletTranslateY' => 0,
            'desktopTranslateY' => 10,
        ]);

        $this->assertStringContainsString('translate-y-5', $classes);
        $this->assertStringContainsString('@3xl:translate-y-0', $classes);
        $this->assertStringContainsString('@5xl:translate-y-10', $classes);
    }

    /** @test */
    public function it_only_generates_responsive_classes_when_values_differ(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileRotate' => 45,
            'tabletRotate' => 45,
            'desktopRotate' => 45,
        ]);

        // Should only have one rotate class, not responsive versions
        $this->assertStringContainsString('rotate-45', $classes);
        $this->assertStringNotContainsString('@3xl:rotate-45', $classes);
        $this->assertStringNotContainsString('@5xl:rotate-45', $classes);
    }

    /** @test */
    public function it_combines_multiple_transforms(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileRotate' => 45,
            'mobileScale' => 1.5,
            'mobileTranslateX' => 10,
            'mobileTranslateY' => 20,
            'mobileSkewX' => 6,
            'mobileSkewY' => 3,
            'tabletRotate' => 45,
            'tabletScale' => 1.5,
            'tabletTranslateX' => 10,
            'tabletTranslateY' => 20,
            'tabletSkewX' => 6,
            'tabletSkewY' => 3,
            'desktopRotate' => 45,
            'desktopScale' => 1.5,
            'desktopTranslateX' => 10,
            'desktopTranslateY' => 20,
            'desktopSkewX' => 6,
            'desktopSkewY' => 3,
        ]);

        $this->assertStringContainsString('rotate-45', $classes);
        $this->assertStringContainsString('scale-150', $classes);
        $this->assertStringContainsString('translate-x-10', $classes);
        $this->assertStringContainsString('translate-y-20', $classes);
        $this->assertStringContainsString('skew-x-6', $classes);
        $this->assertStringContainsString('skew-y-3', $classes);
    }

    /** @test */
    public function it_handles_decimal_rotate_values(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileRotate' => 22.5,
            'tabletRotate' => 22.5,
            'desktopRotate' => 22.5,
        ]);

        $this->assertStringContainsString('rotate-[22.5deg]', $classes);
    }

    /** @test */
    public function it_handles_decimal_scale_values(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileScale' => 0.85,
            'tabletScale' => 0.85,
            'desktopScale' => 0.85,
        ]);

        $this->assertStringContainsString('scale-[85]', $classes);
    }

    /** @test */
    public function it_handles_negative_skew_values(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileSkewX' => -12,
            'tabletSkewX' => -12,
            'desktopSkewX' => -12,
        ]);

        $this->assertStringContainsString('-skew-x-12', $classes);
    }

    /** @test */
    public function it_handles_complex_responsive_scenario(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileRotate' => 0,
            'tabletRotate' => 45,
            'desktopRotate' => 45,
            'mobileScale' => 1,
            'tabletScale' => 1.2,
            'desktopScale' => 1.5,
            'mobileTranslateX' => -10,
            'tabletTranslateX' => 0,
            'desktopTranslateX' => 10,
        ]);

        // Rotate: mobile = 0, tablet = 45, desktop = 45 (same as tablet)
        $this->assertStringContainsString('@3xl:rotate-45', $classes);
        $this->assertStringNotContainsString('@5xl:rotate', $classes);

        // Scale: mobile = 1, tablet = 1.2, desktop = 1.5
        $this->assertStringContainsString('@3xl:scale-[120]', $classes);
        $this->assertStringContainsString('@5xl:scale-150', $classes);

        // TranslateX: mobile = -10, tablet = 0, desktop = 10
        $this->assertStringContainsString('-translate-x-10', $classes);
        $this->assertStringContainsString('@3xl:translate-x-0', $classes);
        $this->assertStringContainsString('@5xl:translate-x-10', $classes);
    }

    /** @test */
    public function it_integrates_transform_classes_with_other_properties(): void
    {
        $classes = $this->service->getCssClassesFromProperties([
            'mobileRotate' => 45,
            'tabletRotate' => 45,
            'desktopRotate' => 45,
            'mobileWidth' => 'w-full',
            'tabletWidth' => 'w-full',
            'desktopWidth' => 'w-full',
            'backgroundColor' => 'blue-500',
        ]);

        $this->assertStringContainsString('rotate-45', $classes);
        $this->assertStringContainsString('w-full', $classes);
        $this->assertStringContainsString('bg-blue-500', $classes);
    }

    /** @test */
    public function it_returns_empty_string_when_no_transforms(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileRotate' => 0,
            'tabletRotate' => 0,
            'desktopRotate' => 0,
            'mobileScale' => 1,
            'tabletScale' => 1,
            'desktopScale' => 1,
        ]);

        $this->assertEmpty($classes);
    }

    /** @test */
    public function it_handles_missing_transform_properties(): void
    {
        // Should not throw an error when properties are missing
        $classes = $this->service->getTransformCssClassesFromProperties([]);

        $this->assertEmpty($classes);
    }

    /** @test */
    public function it_handles_fractional_translate_values(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileTranslateX' => 2.5,
            'tabletTranslateX' => 2.5,
            'desktopTranslateX' => 2.5,
        ]);

        $this->assertStringContainsString('translate-x-2.5', $classes);
    }

    /** @test */
    public function it_handles_zero_scale_correctly(): void
    {
        $classes = $this->service->getTransformCssClassesFromProperties([
            'mobileScale' => 0,
            'tabletScale' => 0,
            'desktopScale' => 0,
        ]);

        $this->assertStringContainsString('scale-0', $classes);
    }
}
