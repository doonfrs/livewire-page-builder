<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties\MarginProperty;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties\PaddingProperty;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class SpacingPropertiesTest extends TestCase
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

    protected function getPaddingProperty(): array
    {
        return [
            'name' => 'spacing_padding',
            'label' => 'Padding',
            'type' => 'padding',
            'paddingClasses' => [
                '' => 'None',
                'custom' => 'Custom',
                'p-0' => '0',
                'p-1' => '1',
                'p-2' => '2',
                'p-4' => '4',
                'p-8' => '8',
            ],
            'unit' => 'px',
        ];
    }

    /** @test */
    public function margin_component_initializes_with_unified_mode_box_model(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getMarginProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        $component->assertSet('mode', 'unified');
        $component->assertSet('activeDevice', 'mobile');

        // Check box model arrays are initialized for unified mode
        $component->assertSet('unifiedClassValues', ['top' => '', 'right' => '', 'bottom' => '', 'left' => '']);
        $component->assertSet('unifiedCustomValues', ['top' => '', 'right' => '', 'bottom' => '', 'left' => '']);

        // Check individual mode arrays are initialized
        $expectedIndividualStructure = [
            'mobile' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
            'tablet' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
            'desktop' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
        ];

        $component->assertSet('individualClassValues', $expectedIndividualStructure);
        $component->assertSet('individualCustomValues', $expectedIndividualStructure);
    }

    /** @test */
    public function padding_component_initializes_with_unified_mode_box_model(): void
    {
        $component = Livewire::test(PaddingProperty::class, [
            'property' => $this->getPaddingProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        $component->assertSet('mode', 'unified');
        $component->assertSet('activeDevice', 'mobile');

        // Check box model arrays are initialized
        $component->assertSet('unifiedClassValues', ['top' => '', 'right' => '', 'bottom' => '', 'left' => '']);
        $component->assertSet('unifiedCustomValues', ['top' => '', 'right' => '', 'bottom' => '', 'left' => '']);
    }

    /** @test */
    public function unified_mode_can_set_individual_edges(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getMarginProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set different values for different edges in unified mode
        $component->set('mode', 'unified');
        $component->set('unifiedClassValues.top', 'm-2');
        $component->set('unifiedClassValues.bottom', 'm-4');

        $component->assertSet('unifiedClassValues.top', 'm-2');
        $component->assertSet('unifiedClassValues.bottom', 'm-4');
        $component->assertDispatched('updateBlockProperty');
    }

    /** @test */
    public function unified_mode_can_set_custom_values(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getMarginProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set custom value for bottom margin in unified mode
        $component->set('mode', 'unified');
        $component->set('unifiedClassValues.bottom', 'custom');
        $component->set('unifiedCustomValues.bottom', '24');

        $component->assertSet('unifiedClassValues.bottom', 'custom');
        $component->assertSet('unifiedCustomValues.bottom', '24');
        $component->assertDispatched('updateBlockProperty');
    }

    /** @test */
    public function individual_mode_can_set_device_specific_values(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getMarginProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Switch to individual mode and set device-specific values
        $component->set('mode', 'individual');
        $component->set('activeDevice', 'mobile');

        // Set mobile margin
        $component->set('individualClassValues.mobile.bottom', 'm-1');

        // Switch to tablet and set different value
        $component->set('activeDevice', 'tablet');
        $component->set('individualClassValues.tablet.bottom', 'm-2');

        // Switch to desktop and set custom value
        $component->set('activeDevice', 'desktop');
        $component->set('individualClassValues.desktop.bottom', 'custom');
        $component->set('individualCustomValues.desktop.bottom', '16');

        $component->assertSet('individualClassValues.mobile.bottom', 'm-1');
        $component->assertSet('individualClassValues.tablet.bottom', 'm-2');
        $component->assertSet('individualClassValues.desktop.bottom', 'custom');
        $component->assertSet('individualCustomValues.desktop.bottom', '16');
        $component->assertDispatched('updateBlockProperty');
    }

    /** @test */
    public function loads_existing_values_and_detects_unified_vs_individual(): void
    {
        // Test case 1: Values that can be unified (same across devices)
        $unifiedValues = [
            'mobileMarginBottom' => '4',
            'tabletMarginBottom' => '4',
            'desktopMarginBottom' => '4',
        ];

        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getMarginProperty(),
            'value' => $unifiedValues,
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Should detect unified values and set unified mode
        $component->assertSet('mode', 'unified');
        $component->assertSet('unifiedClassValues.bottom', 'm-4'); // Should find matching class

        // Test case 2: Values that differ across devices (individual)
        $individualValues = [
            'mobileMarginBottom' => '1',
            'tabletMarginBottom' => '2',
            'desktopMarginBottom' => '4',
        ];

        $component2 = Livewire::test(MarginProperty::class, [
            'property' => $this->getMarginProperty(),
            'value' => $individualValues,
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Should detect individual values and set individual mode
        $component2->assertSet('mode', 'individual');
        $component2->assertSet('individualCustomValues.mobile.bottom', '1');
        $component2->assertSet('individualCustomValues.tablet.bottom', '2');
        $component2->assertSet('individualCustomValues.desktop.bottom', '4');
    }

    /** @test */
    public function loads_custom_values_correctly(): void
    {
        $customValues = [
            'mobileMarginBottom' => '35', // Custom value not in standard classes
            'tabletMarginBottom' => '35',
            'desktopMarginBottom' => '35',
        ];

        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getMarginProperty(),
            'value' => $customValues,
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Should detect unified values and use custom
        $component->assertSet('mode', 'unified');
        $component->assertSet('unifiedClassValues.bottom', 'custom');
        $component->assertSet('unifiedCustomValues.bottom', '35');
    }

    /** @test */
    public function switching_modes_clears_values(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getMarginProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set values in unified mode
        $component->set('mode', 'unified');
        $component->set('unifiedClassValues.bottom', 'm-4');

        // Switch to individual mode - should clear unified values
        $component->set('mode', 'individual');

        $component->assertSet('unifiedClassValues', ['top' => '', 'right' => '', 'bottom' => '', 'left' => '']);
        $component->assertSet('unifiedCustomValues', ['top' => '', 'right' => '', 'bottom' => '', 'left' => '']);

        // Set values in individual mode
        $component->set('individualClassValues.mobile.bottom', 'm-2');

        // Switch back to unified - should clear individual values
        $component->set('mode', 'unified');

        $expectedClearedStructure = [
            'mobile' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
            'tablet' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
            'desktop' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
        ];

        $component->assertSet('individualClassValues', $expectedClearedStructure);
        $component->assertSet('individualCustomValues', $expectedClearedStructure);
    }

    /** @test */
    public function dispatches_correct_property_names_for_all_devices(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getMarginProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set unified bottom margin
        $component->set('mode', 'unified');
        $component->set('unifiedClassValues.bottom', 'm-4');

        // Should dispatch updateBlockProperty events for all devices
        $component->assertDispatched('updateBlockProperty');
    }
}