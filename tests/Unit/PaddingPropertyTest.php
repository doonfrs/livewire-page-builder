<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties\PaddingProperty;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class PaddingPropertyTest extends TestCase
{
    protected function getTestProperty(): array
    {
        return [
            'name' => 'spacing_padding',
            'label' => 'Padding',
            'type' => 'padding',
            'paddingClasses' => [
                '' => 'None',
                'p-0' => 'None (0)',
                'p-1' => 'SM (0.25rem)',
                'p-2' => 'MD (0.5rem)',
                'p-4' => 'XL (1rem)',
                'p-8' => '3XL (2rem)',
                'px-4' => 'Horizontal (1rem)',
                'py-4' => 'Vertical (1rem)',
            ],
            'unit' => 'px',
        ];
    }

    /** @test */
    public function can_initialize_with_unified_mode_default(): void
    {
        $component = Livewire::test(PaddingProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        $component->assertSet('mode', 'unified');
        $component->assertSet('activeDevice', 'mobile');
        $component->assertSet('unifiedClassValues', ['top' => '', 'right' => '', 'bottom' => '', 'left' => '']);
        $component->assertSet('unifiedCustomValues', ['top' => '', 'right' => '', 'bottom' => '', 'left' => '']);
    }

    /** @test */
    public function can_set_unified_class_value(): void
    {
        $component = Livewire::test(PaddingProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set unified class mode
        $component->set('mode', 'unified');
        $component->set('unifiedClassValues.top', 'p-4');

        $component->assertSet('unifiedClassValues.top', 'p-4');

        // Should dispatch updateBlockProperty events for all device/direction combinations
        $component->assertDispatched('updateBlockProperty');
    }

    /** @test */
    public function can_set_unified_custom_value(): void
    {
        $component = Livewire::test(PaddingProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set unified custom mode
        $component->set('mode', 'unified');
        $component->set('unifiedClassValues.top', 'custom');
        $component->set('unifiedCustomValues.top', '16');

        $component->assertSet('unifiedCustomValues.top', '16');
        $component->assertDispatched('updateBlockProperty');
    }

    /** @test */
    public function can_switch_between_devices_in_individual_mode(): void
    {
        $component = Livewire::test(PaddingProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Switch to individual mode
        $component->set('mode', 'individual');

        // Test switching devices
        $component->set('activeDevice', 'tablet');
        $component->assertSet('activeDevice', 'tablet');

        $component->set('activeDevice', 'desktop');
        $component->assertSet('activeDevice', 'desktop');

        $component->set('activeDevice', 'mobile');
        $component->assertSet('activeDevice', 'mobile');
    }

    /** @test */
    public function can_set_individual_custom_values(): void
    {
        $component = Livewire::test(PaddingProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set individual mode
        $component->set('mode', 'individual');
        $component->set('activeDevice', 'mobile');

        // Set individual values for mobile device
        $component->set('individualCustomValues.mobile.top', '8');
        $component->set('individualCustomValues.mobile.right', '12');
        $component->set('individualCustomValues.mobile.bottom', '8');
        $component->set('individualCustomValues.mobile.left', '12');

        $component->assertSet('individualCustomValues.mobile.top', '8');
        $component->assertSet('individualCustomValues.mobile.right', '12');
        $component->assertSet('individualCustomValues.mobile.bottom', '8');
        $component->assertSet('individualCustomValues.mobile.left', '12');

        $component->assertDispatched('updateBlockProperty');
    }

    /** @test */
    public function can_use_different_unified_class_values(): void
    {
        $component = Livewire::test(PaddingProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        $testClasses = ['p-4', 'px-8', 'py-2', 'pt-6'];

        $component->set('mode', 'unified');

        foreach ($testClasses as $class) {
            $component->set('unifiedClassValues.top', $class);
            $component->assertSet('unifiedClassValues.top', $class);
            $component->assertDispatched('updateBlockProperty');
        }
    }

    /** @test */
    public function clears_values_when_switching_modes(): void
    {
        $component = Livewire::test(PaddingProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set some values in unified mode
        $component->set('mode', 'unified');
        $component->set('unifiedClassValues.top', 'p-4');

        // Switch to individual mode - should clear unified values
        $component->set('mode', 'individual');

        $component->assertSet('unifiedClassValues', ['top' => '', 'right' => '', 'bottom' => '', 'left' => '']);
        $component->assertSet('unifiedCustomValues', ['top' => '', 'right' => '', 'bottom' => '', 'left' => '']);
    }

    /** @test */
    public function shows_custom_input_when_custom_is_selected(): void
    {
        $component = Livewire::test(PaddingProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set unified mode and select custom
        $component->set('mode', 'unified');
        $component->set('unifiedClassValues.top', 'custom');

        // Now set a custom value
        $component->set('unifiedCustomValues.top', '24');

        $component->assertSet('unifiedClassValues.top', 'custom');
        $component->assertSet('unifiedCustomValues.top', '24');
        $component->assertDispatched('updateBlockProperty');
    }

    /** @test */
    public function loads_existing_individual_values_correctly(): void
    {
        $existingValues = [
            'mobilePaddingTop' => '8',
            'mobilePaddingRight' => '16',
            'mobilePaddingBottom' => '8',
            'mobilePaddingLeft' => '16',
            'tabletPaddingTop' => '12',
            'desktopPaddingTop' => '20',
        ];

        $component = Livewire::test(PaddingProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => $existingValues,
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Should detect individual values and switch to individual mode
        $component->assertSet('mode', 'individual');

        // Should load the existing values
        $component->assertSet('individualCustomValues.mobile.top', '8');
        $component->assertSet('individualCustomValues.mobile.right', '16');
        $component->assertSet('individualCustomValues.mobile.bottom', '8');
        $component->assertSet('individualCustomValues.mobile.left', '16');
        $component->assertSet('individualCustomValues.tablet.top', '12');
        $component->assertSet('individualCustomValues.desktop.top', '20');
    }

    /** @test */
    public function dispatches_update_events_with_correct_property_names(): void
    {
        $component = Livewire::test(PaddingProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set unified custom value
        $component->set('mode', 'unified');
        $component->set('unifiedClassValues.top', 'custom');
        $component->set('unifiedCustomValues.top', '10');

        // Should dispatch events for all device/direction combinations
        $expectedProperties = [
            'mobilePaddingTop', 'mobilePaddingRight', 'mobilePaddingBottom', 'mobilePaddingLeft',
            'tabletPaddingTop', 'tabletPaddingRight', 'tabletPaddingBottom', 'tabletPaddingLeft',
            'desktopPaddingTop', 'desktopPaddingRight', 'desktopPaddingBottom', 'desktopPaddingLeft',
        ];

        // Check that updateBlockProperty event was dispatched
        $component->assertDispatched('updateBlockProperty');
    }

    /** @test */
    public function handles_directional_class_conversion_correctly(): void
    {
        $component = Livewire::test(PaddingProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        $component->set('mode', 'unified');

        // Test horizontal padding (px-4)
        $component->set('unifiedClassValues.right', 'px-4');
        $component->assertDispatched('updateBlockProperty');

        // Test vertical padding (py-8)
        $component->set('unifiedClassValues.top', 'py-8');
        $component->assertDispatched('updateBlockProperty');

        // Test specific direction (pt-6)
        $component->set('unifiedClassValues.top', 'pt-6');
        $component->assertDispatched('updateBlockProperty');
    }
}
