<?php

namespace Trinavo\LivewirePageBuilder\Tests\Integration;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class SpacingComponentsIntegrationTest extends TestCase
{
    /** @test */
    public function padding_property_component_can_be_registered_and_instantiated(): void
    {
        // Test that the component can be instantiated
        $property = [
            'name' => 'spacing_padding',
            'label' => 'Padding',
            'type' => 'padding',
            'paddingClasses' => [
                '' => 'None',
                'p-4' => 'Standard',
            ],
            'unit' => 'px',
        ];

        $component = Livewire::test('block-properties.padding-property', [
            'property' => $property,
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        $component->assertStatus(200);
        $component->assertSet('mode', 'unified');
    }

    /** @test */
    public function margin_property_component_can_be_registered_and_instantiated(): void
    {
        // Test that the component can be instantiated
        $property = [
            'name' => 'spacing_margin',
            'label' => 'Margin',
            'type' => 'margin',
            'marginClasses' => [
                '' => 'None',
                'm-4' => 'Standard',
                'm-auto' => 'Auto',
            ],
            'unit' => 'px',
        ];

        $component = Livewire::test('block-properties.margin-property', [
            'property' => $property,
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        $component->assertStatus(200);
        $component->assertSet('mode', 'unified');
    }

    /** @test */
    public function components_can_render_their_blade_views(): void
    {
        $paddingProperty = [
            'name' => 'spacing_padding',
            'label' => 'Padding',
            'type' => 'padding',
            'paddingClasses' => [
                '' => 'None',
                'p-4' => 'Standard',
            ],
            'unit' => 'px',
        ];

        $marginProperty = [
            'name' => 'spacing_margin',
            'label' => 'Margin',
            'type' => 'margin',
            'marginClasses' => [
                '' => 'None',
                'm-4' => 'Standard',
            ],
            'unit' => 'px',
        ];

        // Test padding component renders
        $paddingComponent = Livewire::test('block-properties.padding-property', [
            'property' => $paddingProperty,
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        $paddingComponent->assertSee('Padding');
        $paddingComponent->assertSee('Unified');
        $paddingComponent->assertSee('Individual');

        // Test margin component renders
        $marginComponent = Livewire::test('block-properties.margin-property', [
            'property' => $marginProperty,
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        $marginComponent->assertSee('Margin');
        $marginComponent->assertSee('Unified');
        $marginComponent->assertSee('Individual');
    }

    /** @test */
    public function components_show_device_tabs_in_individual_mode(): void
    {
        $property = [
            'name' => 'spacing_padding',
            'label' => 'Padding',
            'type' => 'padding',
            'paddingClasses' => ['' => 'None', 'p-4' => 'Standard'],
            'unit' => 'px',
        ];

        $component = Livewire::test('block-properties.padding-property', [
            'property' => $property,
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Switch to individual mode
        $component->set('mode', 'individual');

        // Should show device tabs
        $component->assertSee('Mobile');
        $component->assertSee('Tablet');
        $component->assertSee('Desktop');
    }

    /** @test */
    public function components_show_box_model_visual_in_individual_mode(): void
    {
        $property = [
            'name' => 'spacing_padding',
            'label' => 'Padding',
            'type' => 'padding',
            'paddingClasses' => ['' => 'None', 'p-4' => 'Standard'],
            'unit' => 'px',
        ];

        $component = Livewire::test('block-properties.padding-property', [
            'property' => $property,
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Switch to individual mode
        $component->set('mode', 'individual');

        // Should show directional labels
        $component->assertSee('Top');
        $component->assertSee('Right');
        $component->assertSee('Bottom');
        $component->assertSee('Left');
        $component->assertSee('Content'); // Center content box
    }

    /** @test */
    public function components_handle_property_updates_correctly(): void
    {
        $property = [
            'name' => 'spacing_padding',
            'label' => 'Padding',
            'type' => 'padding',
            'paddingClasses' => [
                '' => 'None',
                'p-4' => 'Standard',
                'p-8' => 'Large',
            ],
            'unit' => 'px',
        ];

        $component = Livewire::test('block-properties.padding-property', [
            'property' => $property,
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set a unified class value
        $component->set('unifiedClassValue', 'p-4');

        // Should dispatch update events for all device/direction combinations
        $component->assertDispatched('updateBlockProperty');

        // Switch to custom mode and set custom value
        $component->set('unifiedClassValue', 'custom');
        $component->set('unifiedCustomValue', '16');

        // Should dispatch update events again
        $component->assertDispatched('updateBlockProperty');
    }
}
