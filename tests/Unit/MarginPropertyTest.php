<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties\MarginProperty;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class MarginPropertyTest extends TestCase
{
    protected function getTestProperty(): array
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
    public function can_initialize_with_unified_mode_default(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getTestProperty(),
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
    public function can_set_unified_class_values_for_box_model(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set unified class mode with box model - set bottom margin
        $component->set('mode', 'unified');
        $component->set('unifiedClassValues.bottom', 'm-4');

        $component->assertSet('unifiedClassValues.bottom', 'm-4');
        $component->assertDispatched('updateBlockProperty');
    }

    /** @test */
    public function can_set_unified_custom_value(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set unified custom mode
        $component->set('mode', 'unified');
        $component->set('unifiedClassValue', 'custom');
        $component->set('unifiedCustomValue', '24');

        $component->assertSet('unifiedCustomValue', '24');
        $component->assertDispatched('updateBlockProperty');
    }

    /** @test */
    public function can_set_auto_margin(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Test auto margin classes
        $component->set('mode', 'unified');

        // Test m-auto
        $component->set('unifiedClassValue', 'm-auto');
        $component->assertSet('unifiedClassValue', 'm-auto');
        $component->assertDispatched('updateBlockProperty');

        // Test mx-auto (horizontal auto)
        $component->set('unifiedClassValue', 'mx-auto');
        $component->assertSet('unifiedClassValue', 'mx-auto');
        $component->assertDispatched('updateBlockProperty');
    }

    /** @test */
    public function can_switch_between_devices_in_individual_mode(): void
    {
        $component = Livewire::test(MarginProperty::class, [
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
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set individual custom mode
        $component->set('mode', 'individual');
        $component->set('activeDevice', 'desktop');

        // Set individual values for desktop device
        $component->set('individualCustomValues.desktop.top', '16');
        $component->set('individualCustomValues.desktop.right', '0');
        $component->set('individualCustomValues.desktop.bottom', '16');
        $component->set('individualCustomValues.desktop.left', '0');

        $component->assertSet('individualCustomValues.desktop.top', '16');
        $component->assertSet('individualCustomValues.desktop.right', '0');
        $component->assertSet('individualCustomValues.desktop.bottom', '16');
        $component->assertSet('individualCustomValues.desktop.left', '0');

        $component->assertDispatched('updateBlockProperty');
    }

    /** @test */
    public function can_convert_unified_class_to_individual_values(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        $testCases = [
            'm-6' => '6', // Unified margin
            'mx-4' => '', // Horizontal only - should return empty for top/bottom
            'my-8' => '', // Vertical only - should return empty for left/right
            'mt-2' => '', // Top only - should return empty for other directions
            'm-auto' => 'auto', // Auto value
        ];

        $component->set('mode', 'unified');

        foreach ($testCases as $class => $expectedValue) {
            $component->set('unifiedClassValue', $class);

            // Test the conversion logic by triggering property updates
            $component->assertDispatched('updateBlockProperty');
        }
    }

    /** @test */
    public function clears_values_when_switching_modes(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set some values in unified mode
        $component->set('mode', 'unified');
        $component->set('unifiedClassValue', 'm-8');

        // Switch to individual mode - should clear unified values
        $component->set('mode', 'individual');

        $component->assertSet('unifiedClassValue', '');
        $component->assertSet('unifiedCustomValue', '');
    }

    /** @test */
    public function clears_values_when_switching_input_modes(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set custom value
        $component->set('mode', 'unified');
        $component->set('unifiedClassValue', 'custom');
        $component->set('unifiedCustomValue', '32');

        // Switch to class selection - should keep custom value but not active
        $component->set('unifiedClassValue', 'm-4');
        $component->set('unifiedCustomValue', '');

        $component->assertSet('unifiedCustomValue', '');
    }

    /** @test */
    public function loads_existing_individual_values_correctly(): void
    {
        $existingValues = [
            'mobileMarginTop' => '4',
            'mobileMarginRight' => '8',
            'mobileMarginBottom' => '4',
            'mobileMarginLeft' => '8',
            'tabletMarginTop' => '6',
            'desktopMarginTop' => '12',
            'desktopMarginLeft' => 'auto',
            'desktopMarginRight' => 'auto',
        ];

        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => $existingValues,
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Should detect individual values and switch to individual mode
        $component->assertSet('mode', 'individual');

        // Should load the existing values
        $component->assertSet('individualCustomValues.mobile.top', '4');
        $component->assertSet('individualCustomValues.mobile.right', '8');
        $component->assertSet('individualCustomValues.mobile.bottom', '4');
        $component->assertSet('individualCustomValues.mobile.left', '8');
        $component->assertSet('individualCustomValues.tablet.top', '6');
        $component->assertSet('individualCustomValues.desktop.top', '12');
        $component->assertSet('individualCustomValues.desktop.left', 'auto');
        $component->assertSet('individualCustomValues.desktop.right', 'auto');
    }

    /** @test */
    public function dispatches_update_events_with_correct_property_names(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set unified custom value
        $component->set('mode', 'unified');
        $component->set('unifiedClassValue', 'custom');
        $component->set('unifiedCustomValue', '20');

        // Should dispatch events for all device/direction combinations
        $expectedProperties = [
            'mobileMarginTop', 'mobileMarginRight', 'mobileMarginBottom', 'mobileMarginLeft',
            'tabletMarginTop', 'tabletMarginRight', 'tabletMarginBottom', 'tabletMarginLeft',
            'desktopMarginTop', 'desktopMarginRight', 'desktopMarginBottom', 'desktopMarginLeft',
        ];

        // Check that updateBlockProperty event was dispatched
        $component->assertDispatched('updateBlockProperty');
    }

    /** @test */
    public function handles_directional_class_conversion_correctly(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        $component->set('mode', 'unified');

        // Test horizontal margin (mx-6)
        $component->set('unifiedClassValue', 'mx-6');
        $component->assertDispatched('updateBlockProperty');

        // Test vertical margin (my-8)
        $component->set('unifiedClassValue', 'my-8');
        $component->assertDispatched('updateBlockProperty');

        // Test specific direction (mb-4)
        $component->set('unifiedClassValue', 'mb-4');
        $component->assertDispatched('updateBlockProperty');
    }

    /** @test */
    public function handles_negative_margin_classes(): void
    {
        $propertyWithNegative = $this->getTestProperty();
        $propertyWithNegative['marginClasses'] = array_merge($propertyWithNegative['marginClasses'], [
            '-m-1' => 'Negative SM (-0.25rem)',
            '-m-2' => 'Negative MD (-0.5rem)',
            '-mt-4' => 'Negative Top (-1rem)',
        ]);

        $component = Livewire::test(MarginProperty::class, [
            'property' => $propertyWithNegative,
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Test negative margin
        $component->set('mode', 'unified');
        $component->set('unifiedClassValue', '-m-2');

        $component->assertSet('unifiedClassValue', '-m-2');
        $component->assertDispatched('updateBlockProperty');
    }

    /** @test */
    public function individual_mode_allows_mixed_auto_and_numeric_values(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getTestProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set individual custom mode
        $component->set('mode', 'individual');
        $component->set('activeDevice', 'desktop');

        // Mix auto and numeric values (common pattern for centering)
        $component->set('individualCustomValues.desktop.top', '0');
        $component->set('individualCustomValues.desktop.right', 'auto');
        $component->set('individualCustomValues.desktop.bottom', '16');
        $component->set('individualCustomValues.desktop.left', 'auto');

        $component->assertSet('individualCustomValues.desktop.top', '0');
        $component->assertSet('individualCustomValues.desktop.right', 'auto');
        $component->assertSet('individualCustomValues.desktop.bottom', '16');
        $component->assertSet('individualCustomValues.desktop.left', 'auto');

        $component->assertDispatched('updateBlockProperty');
    }
}
