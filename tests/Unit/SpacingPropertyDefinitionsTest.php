<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Trinavo\LivewirePageBuilder\Support\Properties\MarginProperty;
use Trinavo\LivewirePageBuilder\Support\Properties\PaddingProperty;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class SpacingPropertyDefinitionsTest extends TestCase
{
    /** @test */
    public function padding_property_has_correct_type(): void
    {
        $property = new PaddingProperty('test_padding', 'Test Padding');

        $this->assertEquals('padding', $property->getType());
    }

    /** @test */
    public function margin_property_has_correct_type(): void
    {
        $property = new MarginProperty('test_margin', 'Test Margin');

        $this->assertEquals('margin', $property->getType());
    }

    /** @test */
    public function padding_property_can_be_converted_to_array(): void
    {
        $property = new PaddingProperty(
            name: 'spacing_padding',
            label: 'Padding Control',
            paddingClasses: ['p-4' => 'Standard'],
            unit: 'rem'
        );

        $array = $property->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('spacing_padding', $array['name']);
        $this->assertEquals('Padding Control', $array['label']);
        $this->assertEquals('padding', $array['type']);
        $this->assertEquals(['p-4' => 'Standard'], $array['paddingClasses']);
        $this->assertEquals('rem', $array['unit']);
        $this->assertArrayHasKey('group', $array);
        $this->assertArrayHasKey('groupLabel', $array);
        $this->assertArrayHasKey('groupIcon', $array);
        $this->assertArrayHasKey('groupColumns', $array);
    }

    /** @test */
    public function margin_property_can_be_converted_to_array(): void
    {
        $property = new MarginProperty(
            name: 'spacing_margin',
            label: 'Margin Control',
            marginClasses: ['m-auto' => 'Auto'],
            unit: 'px'
        );

        $array = $property->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('spacing_margin', $array['name']);
        $this->assertEquals('Margin Control', $array['label']);
        $this->assertEquals('margin', $array['type']);
        $this->assertEquals(['m-auto' => 'Auto'], $array['marginClasses']);
        $this->assertEquals('px', $array['unit']);
        $this->assertArrayHasKey('group', $array);
        $this->assertArrayHasKey('groupLabel', $array);
        $this->assertArrayHasKey('groupIcon', $array);
        $this->assertArrayHasKey('groupColumns', $array);
    }

    /** @test */
    public function padding_property_uses_default_classes_when_none_provided(): void
    {
        $property = new PaddingProperty('test_padding', 'Test Padding');

        $array = $property->toArray();
        $classes = $array['paddingClasses'];

        $this->assertIsArray($classes);
        $this->assertArrayHasKey('', $classes); // None option
        $this->assertArrayHasKey('p-0', $classes);
        $this->assertArrayHasKey('p-1', $classes);
        $this->assertArrayHasKey('p-4', $classes);
        $this->assertArrayHasKey('px-4', $classes); // Directional
        $this->assertArrayHasKey('py-4', $classes); // Directional
        $this->assertEquals('None', $classes['']);
        $this->assertStringContainsString('1rem', $classes['p-4']);
        $this->assertStringContainsString('Horizontal', $classes['px-4']);
    }

    /** @test */
    public function margin_property_uses_default_classes_when_none_provided(): void
    {
        $property = new MarginProperty('test_margin', 'Test Margin');

        $array = $property->toArray();
        $classes = $array['marginClasses'];

        $this->assertIsArray($classes);
        $this->assertArrayHasKey('', $classes); // None option
        $this->assertArrayHasKey('m-0', $classes);
        $this->assertArrayHasKey('m-1', $classes);
        $this->assertArrayHasKey('m-4', $classes);
        $this->assertArrayHasKey('m-auto', $classes); // Auto
        $this->assertArrayHasKey('mx-auto', $classes); // Auto X
        $this->assertArrayHasKey('my-auto', $classes); // Auto Y
        $this->assertEquals('None', $classes['']);
        $this->assertEquals('Auto', $classes['m-auto']);
        $this->assertStringContainsString('1rem', $classes['m-4']);
    }

    /** @test */
    public function padding_property_can_use_custom_classes(): void
    {
        $customClasses = [
            '' => 'No Padding',
            'p-2' => 'Small',
            'p-6' => 'Large',
            'px-8' => 'Wide',
        ];

        $property = new PaddingProperty(
            name: 'custom_padding',
            label: 'Custom Padding',
            paddingClasses: $customClasses
        );

        $array = $property->toArray();

        $this->assertEquals($customClasses, $array['paddingClasses']);
    }

    /** @test */
    public function margin_property_can_use_custom_classes(): void
    {
        $customClasses = [
            '' => 'No Margin',
            'm-1' => 'Tiny',
            'm-12' => 'Huge',
            '-m-2' => 'Negative',
        ];

        $property = new MarginProperty(
            name: 'custom_margin',
            label: 'Custom Margin',
            marginClasses: $customClasses
        );

        $array = $property->toArray();

        $this->assertEquals($customClasses, $array['marginClasses']);
    }

    /** @test */
    public function properties_can_use_custom_units(): void
    {
        $paddingProperty = new PaddingProperty(
            name: 'rem_padding',
            label: 'REM Padding',
            unit: 'rem'
        );

        $marginProperty = new MarginProperty(
            name: 'em_margin',
            label: 'EM Margin',
            unit: 'em'
        );

        $this->assertEquals('rem', $paddingProperty->toArray()['unit']);
        $this->assertEquals('em', $marginProperty->toArray()['unit']);
    }

    /** @test */
    public function properties_can_be_assigned_to_groups(): void
    {
        $paddingProperty = new PaddingProperty('grouped_padding', 'Grouped Padding');
        $paddingProperty->setGroup('spacing', 'Spacing Controls', 2, 'heroicon-o-squares-plus');

        $marginProperty = new MarginProperty('grouped_margin', 'Grouped Margin');
        $marginProperty->setGroup('layout', 'Layout Controls', 1, 'heroicon-o-rectangle-group');

        $paddingArray = $paddingProperty->toArray();
        $this->assertEquals('spacing', $paddingArray['group']);
        $this->assertEquals('Spacing Controls', $paddingArray['groupLabel']);
        $this->assertEquals(2, $paddingArray['groupColumns']);
        $this->assertEquals('heroicon-o-squares-plus', $paddingArray['groupIcon']);

        $marginArray = $marginProperty->toArray();
        $this->assertEquals('layout', $marginArray['group']);
        $this->assertEquals('Layout Controls', $marginArray['groupLabel']);
        $this->assertEquals(1, $marginArray['groupColumns']);
        $this->assertEquals('heroicon-o-rectangle-group', $marginArray['groupIcon']);
    }

    /** @test */
    public function properties_inherit_from_block_property(): void
    {
        $paddingProperty = new PaddingProperty('test_padding', 'Test Padding');
        $marginProperty = new MarginProperty('test_margin', 'Test Margin');

        // Test that they have the basic BlockProperty functionality
        $this->assertEquals('test_padding', $paddingProperty->name);
        $this->assertEquals('Test Padding', $paddingProperty->label);

        $this->assertEquals('test_margin', $marginProperty->name);
        $this->assertEquals('Test Margin', $marginProperty->label);

        // Test that setGroup method works (inherited from BlockProperty)
        $paddingProperty->setGroup('test_group', 'Test Group');
        $this->assertEquals('test_group', $paddingProperty->group);
        $this->assertEquals('Test Group', $paddingProperty->groupLabel);
    }

    /** @test */
    public function properties_have_sensible_defaults(): void
    {
        $paddingProperty = new PaddingProperty('default_padding', 'Default Padding');
        $marginProperty = new MarginProperty('default_margin', 'Default Margin');

        $paddingArray = $paddingProperty->toArray();
        $marginArray = $marginProperty->toArray();

        // Should default to px unit
        $this->assertEquals('px', $paddingArray['unit']);
        $this->assertEquals('px', $marginArray['unit']);

        // Should have no default value
        $this->assertNull($paddingArray['defaultValue']);
        $this->assertNull($marginArray['defaultValue']);

        // Should have reasonable class options
        $this->assertGreaterThan(10, count($paddingArray['paddingClasses']));
        $this->assertGreaterThan(10, count($marginArray['marginClasses']));
    }
}
