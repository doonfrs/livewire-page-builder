<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class PageBuilderServiceSpacingTest extends TestCase
{
    protected PageBuilderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PageBuilderService::class);
    }

    /** @test */
    public function mobile_only_padding_generates_correct_classes(): void
    {
        $properties = [
            'mobilePaddingBottom' => '24',
            'tabletPaddingBottom' => '0',
            'desktopPaddingBottom' => '0',
        ];

        $classes = $this->service->getCssClassesFromProperties($properties);

        // Should generate mobile padding with tablet/desktop reset
        // Standard value 24 should use pb-24, not pb-[24px]
        $this->assertStringContainsString('pb-24', $classes);
        $this->assertStringContainsString('@3xl:pb-0', $classes);
    }

    /** @test */
    public function mobile_only_margin_generates_correct_classes(): void
    {
        $properties = [
            'mobileMarginBottom' => '1',
            'tabletMarginBottom' => '0',
            'desktopMarginBottom' => '0',
        ];

        $classes = $this->service->getCssClassesFromProperties($properties);

        // Should generate mobile margin with tablet/desktop reset
        $this->assertStringContainsString('mb-1', $classes);
        $this->assertStringContainsString('@3xl:mb-0', $classes);
    }

    /** @test */
    public function device_specific_different_values_generate_correct_classes(): void
    {
        $properties = [
            'mobileMarginBottom' => '1',
            'tabletMarginBottom' => '2',
            'desktopMarginBottom' => '4',
        ];

        $classes = $this->service->getCssClassesFromProperties($properties);

        // Should generate progression: mobile -> tablet -> desktop
        $this->assertStringContainsString('mb-1', $classes); // mobile
        $this->assertStringContainsString('@3xl:mb-2', $classes); // tablet
        $this->assertStringContainsString('@5xl:mb-4', $classes); // desktop
    }

    /** @test */
    public function same_values_across_devices_generate_single_class(): void
    {
        $properties = [
            'mobileMarginBottom' => '4',
            'tabletMarginBottom' => '4',
            'desktopMarginBottom' => '4',
        ];

        $classes = $this->service->getCssClassesFromProperties($properties);

        // Should only generate mobile class since values are the same
        $this->assertStringContainsString('mb-4', $classes);
        $this->assertStringNotContainsString('@3xl:mb', $classes);
        $this->assertStringNotContainsString('@5xl:mb', $classes);
    }

    /** @test */
    public function custom_values_generate_arbitrary_classes(): void
    {
        $properties = [
            'mobilePaddingBottom' => '35',
            'tabletPaddingBottom' => '0',
            'desktopPaddingBottom' => '0',
        ];

        $classes = $this->service->getCssClassesFromProperties($properties);

        // Should generate arbitrary value classes
        $this->assertStringContainsString('pb-[35px]', $classes);
        $this->assertStringContainsString('@3xl:pb-0', $classes);
    }

    /** @test */
    public function zero_values_reset_correctly(): void
    {
        $properties = [
            'mobileMarginTop' => '8',
            'tabletMarginTop' => '0', // Reset on tablet
            'desktopMarginTop' => '4', // Different value on desktop
        ];

        $classes = $this->service->getCssClassesFromProperties($properties);

        $this->assertStringContainsString('mt-8', $classes); // mobile
        $this->assertStringContainsString('@3xl:mt-0', $classes); // tablet reset
        $this->assertStringContainsString('@5xl:mt-4', $classes); // desktop override
    }

    /** @test */
    public function negative_margins_work_correctly(): void
    {
        $properties = [
            'mobileMarginTop' => '-4',
            'tabletMarginTop' => '0',
            'desktopMarginTop' => '0',
        ];

        $classes = $this->service->getCssClassesFromProperties($properties);

        $this->assertStringContainsString('-mt-4', $classes); // negative mobile
        $this->assertStringContainsString('@3xl:mt-0', $classes); // tablet reset
    }

    /** @test */
    public function mixed_padding_and_margin_work_together(): void
    {
        $properties = [
            'mobilePaddingBottom' => '24',
            'tabletPaddingBottom' => '0',
            'mobileMarginBottom' => '1',
            'tabletMarginBottom' => '2',
            'desktopMarginBottom' => '4',
        ];

        $classes = $this->service->getCssClassesFromProperties($properties);

        // Padding
        $this->assertStringContainsString('pb-[24px]', $classes);
        $this->assertStringContainsString('@3xl:pb-0', $classes);

        // Margin
        $this->assertStringContainsString('mb-1', $classes);
        $this->assertStringContainsString('@3xl:mb-2', $classes);
        $this->assertStringContainsString('@5xl:mb-4', $classes);
    }

    /** @test */
    public function all_directions_work_correctly(): void
    {
        $properties = [
            'mobilePaddingTop' => '1',
            'mobilePaddingRight' => '2',
            'mobilePaddingBottom' => '3',
            'mobilePaddingLeft' => '4',
            'tabletPaddingTop' => '0',
            'tabletPaddingRight' => '0',
            'tabletPaddingBottom' => '0',
            'tabletPaddingLeft' => '0',
        ];

        $classes = $this->service->getCssClassesFromProperties($properties);

        // Mobile padding
        $this->assertStringContainsString('pt-1', $classes);
        $this->assertStringContainsString('pr-2', $classes);
        $this->assertStringContainsString('pb-3', $classes);
        $this->assertStringContainsString('pl-4', $classes);

        // Tablet resets
        $this->assertStringContainsString('@3xl:pt-0', $classes);
        $this->assertStringContainsString('@3xl:pr-0', $classes);
        $this->assertStringContainsString('@3xl:pb-0', $classes);
        $this->assertStringContainsString('@3xl:pl-0', $classes);
    }
}