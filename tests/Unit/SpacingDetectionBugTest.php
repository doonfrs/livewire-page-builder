<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties\MarginProperty;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class SpacingDetectionBugTest extends TestCase
{
    protected function getMarginProperty(): array
    {
        return [
            'name' => 'spacing_margin',
            'label' => 'Margin',
            'type' => 'margin',
            'marginClasses' => [
                '' => 'None',
                'custom' => 'Custom',
                'm-0' => '0',
                'm-1' => '1',
                'm-2' => '2',
                'm-4' => '4',
                'm-8' => '8',
            ],
            'unit' => 'px',
        ];
    }

    /** @test */
    public function user_scenario_mobile_1_tablet_2_desktop_4_should_be_individual(): void
    {
        // User's scenario: mobile=1, tablet=2, desktop=4
        $userValues = [
            'mobileMarginBottom' => '1',
            'tabletMarginBottom' => '2',
            'desktopMarginBottom' => '4',
        ];

        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getMarginProperty(),
            'value' => $userValues,
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // These values are different across devices, should be individual mode
        $component->assertSet('mode', 'individual');

        // Should load individual values correctly
        $component->assertSet('individualCustomValues.mobile.bottom', '1');
        $component->assertSet('individualCustomValues.tablet.bottom', '2');
        $component->assertSet('individualCustomValues.desktop.bottom', '4');

        // Should NOT be in unified mode
        $component->assertNotEquals('unified', $component->get('mode'));
    }

    /** @test */
    public function pagebuilder_service_generates_correct_classes_for_user_scenario(): void
    {
        $service = app(PageBuilderService::class);

        // User's scenario: mobile=1, tablet=2, desktop=4
        $properties = [
            'mobileMarginBottom' => '1',
            'tabletMarginBottom' => '2',
            'desktopMarginBottom' => '4',
        ];

        $classes = $service->getCssClassesFromProperties($properties);

        // Should generate progressive classes
        $this->assertStringContainsString('mb-1', $classes); // mobile
        $this->assertStringContainsString('@3xl:mb-2', $classes); // tablet
        $this->assertStringContainsString('@5xl:mb-4', $classes); // desktop

        // Should NOT generate unified single class
        $this->assertStringNotContainsString('mb-4 @3xl', $classes);
        $this->assertStringNotContainsString('mb-4 @5xl', $classes);
    }

    /** @test */
    public function debug_why_user_sees_unified_mode_with_value_4(): void
    {
        // Test what happens if we have some old data mixed with new data
        $possibleScenario1 = [
            'mobileMarginBottom' => '1',
            'tabletMarginBottom' => '2',
            'desktopMarginBottom' => '4',
            // Old properties that might still exist
            'paddingBottom' => '24',
        ];

        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getMarginProperty(),
            'value' => $possibleScenario1,
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Debug output
        $mode = $component->get('mode');
        $unifiedValues = $component->get('unifiedClassValues');
        $individualValues = $component->get('individualCustomValues');

        // Create debugging info for the user
        echo "\nDEBUG INFO:\n";
        echo "Mode: " . $mode . "\n";
        echo "Unified values: " . json_encode($unifiedValues) . "\n";
        echo "Individual values: " . json_encode($individualValues) . "\n";

        // This should be individual mode
        $component->assertSet('mode', 'individual');
    }

    /** @test */
    public function detect_if_old_properties_interfering(): void
    {
        // Check if old unified properties might be causing issues
        $withOldProperties = [
            'mobileMarginBottom' => '1',
            'tabletMarginBottom' => '2',
            'desktopMarginBottom' => '4',
            // Simulate old properties that might still be in database
            'marginBottom' => '4', // Old unified property
        ];

        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getMarginProperty(),
            'value' => $withOldProperties,
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Should still detect as individual (ignoring old properties)
        $component->assertSet('mode', 'individual');
    }
}