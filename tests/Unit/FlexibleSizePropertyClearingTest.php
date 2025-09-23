<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties\FlexibleSizeProperty;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class FlexibleSizePropertyClearingTest extends TestCase
{
    /** @test */
    public function can_clear_height_property_by_selecting_empty_option(): void
    {
        $property = [
            'name' => 'desktopHeight',
            'label' => 'Desktop',
            'type' => 'flexible-size',
            'classes' => [
                'h-64' => 'Small',
                'h-96' => 'Medium',
                'h-full' => 'Full',
            ],
            'allowCustom' => true,
            'unit' => 'px',
        ];

        $component = Livewire::test(FlexibleSizeProperty::class, [
            'property' => $property,
            'value' => 'h-96', // Start with a value set
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Verify initial state
        $component->assertSet('value', 'h-96');
        $component->assertSet('mode', 'class');
        $component->assertSet('selectedClass', 'h-96');

        // Simulate selecting the empty option to clear the value
        $component->set('selectedClass', '');

        // Verify the value is cleared
        $component->assertSet('value', '');
        $component->assertSet('selectedClass', '');

        // Verify the updateBlockProperty event was dispatched
        $component->assertDispatched('updateBlockProperty');
    }

    /** @test */
    public function can_clear_custom_height_value(): void
    {
        $property = [
            'name' => 'mobileHeight',
            'label' => 'Mobile',
            'type' => 'flexible-size',
            'classes' => [
                'h-64' => 'Small',
                'h-96' => 'Medium',
            ],
            'allowCustom' => true,
            'unit' => 'px',
        ];

        $component = Livewire::test(FlexibleSizeProperty::class, [
            'property' => $property,
            'value' => 'h-[200px]', // Start with custom value
            'rowId' => 'test-row',
            'blockId' => null,
        ]);

        // Verify initial state (should detect custom value)
        $component->assertSet('mode', 'custom');
        $component->assertSet('customValue', '200');

        // Clear the custom value
        $component->set('customValue', '');

        // Verify the value is cleared
        $component->assertSet('value', '');

        // Verify the updateBlockProperty event was dispatched
        $component->assertDispatched('updateBlockProperty');
    }

    /** @test */
    public function handles_switching_from_custom_to_class_mode_and_clearing(): void
    {
        $property = [
            'name' => 'tabletHeight',
            'label' => 'Tablet',
            'type' => 'flexible-size',
            'classes' => [
                'h-64' => 'Small',
                'h-96' => 'Medium',
            ],
            'allowCustom' => true,
            'unit' => 'px',
        ];

        $component = Livewire::test(FlexibleSizeProperty::class, [
            'property' => $property,
            'value' => 'h-[150px]', // Start with custom value
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Verify starts in custom mode
        $component->assertSet('mode', 'custom');
        $component->assertSet('customValue', '150');

        // Switch to class mode
        $component->set('mode', 'class');

        // Verify custom value is cleared but no class selected yet
        $component->assertSet('customValue', '');
        $component->assertSet('selectedClass', '');

        // Now select empty option to clear
        $component->set('selectedClass', '');

        // Verify event is dispatched
        $component->assertDispatched('updateBlockProperty');
    }

    /** @test */
    public function empty_option_text_is_empty(): void
    {
        $property = [
            'name' => 'desktopHeight',
            'label' => 'Desktop',
            'type' => 'flexible-size',
            'classes' => [
                'h-64' => 'Small',
                'h-96' => 'Medium',
            ],
            'allowCustom' => true,
            'unit' => 'px',
        ];

        $component = Livewire::test(FlexibleSizeProperty::class, [
            'property' => $property,
            'value' => null,
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Check that the rendered view has an empty option with no text
        $component->assertSee('<option value=""></option>', false);
        $component->assertDontSee('Select desktop');
        $component->assertDontSee('Select Desktop');
    }

    /** @test */
    public function min_height_properties_work_correctly(): void
    {
        $property = [
            'name' => 'mobileMinHeight',
            'label' => 'Mobile',
            'type' => 'flexible-size',
            'classes' => [
                'min-h-screen' => 'Screen',
                'min-h-full' => 'Full',
            ],
            'allowCustom' => true,
            'unit' => 'px',
        ];

        $component = Livewire::test(FlexibleSizeProperty::class, [
            'property' => $property,
            'value' => 'min-h-screen',
            'rowId' => 'test-row',
            'blockId' => null,
        ]);

        // Verify correct Tailwind prefix is detected
        $component->assertSet('prefix', 'min-h');
        $component->assertSet('mode', 'class');
        $component->assertSet('selectedClass', 'min-h-screen');

        // Clear the value
        $component->set('selectedClass', '');

        // Verify cleared
        $component->assertSet('value', '');
        $component->assertDispatched('updateBlockProperty');
    }
}
