<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class RowContentAlignmentTest extends TestCase
{
    /** @test */
    public function content_center_maps_to_justify_center_when_flex_is_active(): void
    {
        $service = new PageBuilderService;

        $properties = [
            'flex' => 'col',
            'contentAlign' => 'content-center',
        ];

        $cssClasses = $service->getRowCssClassesFromProperties($properties);

        $this->assertStringContainsString('justify-center', $cssClasses);
        $this->assertStringNotContainsString('content-center', $cssClasses);
    }

    /** @test */
    public function content_start_maps_to_justify_start_when_flex_is_active(): void
    {
        $service = new PageBuilderService;

        $properties = [
            'flex' => 'col',
            'contentAlign' => 'content-start',
        ];

        $cssClasses = $service->getRowCssClassesFromProperties($properties);

        $this->assertStringContainsString('justify-start', $cssClasses);
        $this->assertStringNotContainsString('content-start', $cssClasses);
    }

    /** @test */
    public function content_end_maps_to_justify_end_when_flex_is_active(): void
    {
        $service = new PageBuilderService;

        $properties = [
            'flex' => 'col',
            'contentAlign' => 'content-end',
        ];

        $cssClasses = $service->getRowCssClassesFromProperties($properties);

        $this->assertStringContainsString('justify-end', $cssClasses);
        $this->assertStringNotContainsString('content-end', $cssClasses);
    }

    /** @test */
    public function content_stretch_maps_to_justify_stretch_when_flex_is_active(): void
    {
        $service = new PageBuilderService;

        $properties = [
            'flex' => 'col',
            'contentAlign' => 'content-stretch',
        ];

        $cssClasses = $service->getRowCssClassesFromProperties($properties);

        $this->assertStringContainsString('justify-stretch', $cssClasses);
        $this->assertStringNotContainsString('content-stretch', $cssClasses);
    }

    /** @test */
    public function alignment_works_with_row_direction(): void
    {
        $service = new PageBuilderService;

        $properties = [
            'flex' => 'row',
            'contentAlign' => 'content-center',
        ];

        $cssClasses = $service->getRowCssClassesFromProperties($properties);

        $this->assertStringContainsString('flex flex-row', $cssClasses);
        $this->assertStringContainsString('justify-center', $cssClasses);
    }

    /** @test */
    public function no_alignment_classes_when_flex_is_not_active(): void
    {
        $service = new PageBuilderService;

        $properties = [
            'contentAlign' => 'content-center',
            // No flex property
        ];

        $cssClasses = $service->getRowCssClassesFromProperties($properties);

        $this->assertStringNotContainsString('justify-center', $cssClasses);
        $this->assertStringNotContainsString('content-center', $cssClasses);
        $this->assertStringNotContainsString('flex', $cssClasses);
    }

    /** @test */
    public function defaults_to_justify_center_when_unknown_content_align_value(): void
    {
        $service = new PageBuilderService;

        $properties = [
            'flex' => 'col',
            'contentAlign' => 'invalid-value',
        ];

        $cssClasses = $service->getRowCssClassesFromProperties($properties);

        $this->assertStringContainsString('justify-center', $cssClasses);
    }

    /** @test */
    public function defaults_to_justify_center_when_no_content_align_specified(): void
    {
        $service = new PageBuilderService;

        $properties = [
            'flex' => 'col',
            // No contentAlign specified
        ];

        $cssClasses = $service->getRowCssClassesFromProperties($properties);

        $this->assertStringContainsString('justify-center', $cssClasses);
    }

    /** @test */
    public function content_alignment_works_with_other_properties(): void
    {
        $service = new PageBuilderService;

        $properties = [
            'flex' => 'col',
            'contentAlign' => 'content-center',
            'contentWidthMobile' => 'w-full',
            'contentWidthDesktop' => 'w-1/2',
            'overflowX' => 'hidden',
        ];

        $cssClasses = $service->getRowCssClassesFromProperties($properties);

        // Should include alignment
        $this->assertStringContainsString('justify-center', $cssClasses);
        // Should include flex direction
        $this->assertStringContainsString('flex flex-col', $cssClasses);
        // Should include width classes
        $this->assertStringContainsString('w-full', $cssClasses);
        $this->assertStringContainsString('@5xl:w-1/2', $cssClasses);
        // Should include overflow
        $this->assertStringContainsString('overflow-x-hidden', $cssClasses);
    }
}