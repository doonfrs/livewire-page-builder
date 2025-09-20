<?php

namespace Trinavo\LivewirePageBuilder\Tests\Feature;

use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class NestedRowFlexWidthTest extends TestCase
{
    protected PageBuilderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PageBuilderService::class);
    }

    /** @test */
    public function width_classes_are_preserved_for_both_row_blocks_and_regular_blocks(): void
    {
        // Test that width classes are preserved as-is for both types
        $testCases = [
            'w-full',
            'w-auto',
            'w-1/2',
            'w-2/3',
            'w-3/4',
            'w-fit',
            'w-2xs',
            'w-xl',
        ];

        foreach ($testCases as $widthClass) {
            $properties = [
                'desktopWidth' => $widthClass,
                'mobileWidth' => 'w-full',
                'tabletWidth' => 'w-full',
            ];

            // Test for row blocks (should preserve width classes)
            $rowBlockClasses = $this->service->getCssClassesFromProperties($properties, isRowBlock: true);
            $this->assertStringContainsString($widthClass, $rowBlockClasses,
                "Row block with {$widthClass} should contain {$widthClass}");

            // Test for regular blocks (should also preserve width classes)
            $regularBlockClasses = $this->service->getCssClassesFromProperties($properties, isRowBlock: false);
            $this->assertStringContainsString($widthClass, $regularBlockClasses,
                "Regular block with {$widthClass} should contain {$widthClass}");
        }
    }

    /** @test */
    public function arbitrary_width_values_remain_as_width_for_row_blocks(): void
    {
        $properties = [
            'desktopWidth' => 'w-[200px]',
            'mobileWidth' => 'w-[100px]',
            'tabletWidth' => 'w-[150px]',
        ];

        // Even for row blocks, arbitrary values should remain as width
        $rowBlockClasses = $this->service->getCssClassesFromProperties($properties, isRowBlock: true);
        $this->assertStringContainsString('w-[200px]', $rowBlockClasses,
            'Row block with arbitrary width should keep the width class');
        $this->assertStringContainsString('w-[100px]', $rowBlockClasses,
            'Row block with arbitrary mobile width should keep the width class');
    }

    /** @test */
    public function format_size_value_method_preserves_width_classes(): void
    {
        // Use reflection to test the protected method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('formatSizeValue');
        $method->setAccessible(true);

        // Test that width classes are preserved as-is
        $this->assertEquals('w-full', $method->invoke($this->service, 'w-full', 'w'));
        $this->assertEquals('w-auto', $method->invoke($this->service, 'w-auto', 'w'));
        $this->assertEquals('h-full', $method->invoke($this->service, 'h-full', 'h'));
        $this->assertEquals('w-[300px]', $method->invoke($this->service, 'w-[300px]', 'w'));
    }
}