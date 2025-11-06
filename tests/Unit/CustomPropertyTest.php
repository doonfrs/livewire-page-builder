<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Trinavo\LivewirePageBuilder\Support\Properties\CustomProperty;

class CustomPropertyTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_basic_parameters()
    {
        $property = new CustomProperty('test_property', 'Test Label');

        $this->assertInstanceOf(CustomProperty::class, $property);
        $this->assertEquals('test_property', $property->name);
        $this->assertEquals('Test Label', $property->label);
    }

    #[Test]
    public function it_can_be_created_using_make_method()
    {
        $property = CustomProperty::make('test_property', 'Test Label');

        $this->assertInstanceOf(CustomProperty::class, $property);
        $this->assertEquals('test_property', $property->name);
        $this->assertEquals('Test Label', $property->label);
    }

    #[Test]
    public function it_returns_correct_type()
    {
        $property = CustomProperty::make('test_property');

        $this->assertEquals('custom', $property->getType());
    }

    #[Test]
    public function it_can_set_component_class()
    {
        $property = CustomProperty::make('test_property')
            ->component('App\\Livewire\\CustomComponent');

        $this->assertEquals('App\\Livewire\\CustomComponent', $property->component);
    }

    #[Test]
    public function it_can_set_component_via_constructor()
    {
        $property = new CustomProperty(
            'test_property',
            'Test Label',
            'App\\Livewire\\CustomComponent'
        );

        $this->assertEquals('App\\Livewire\\CustomComponent', $property->component);
    }

    #[Test]
    public function it_can_set_configuration_array()
    {
        $config = ['key' => 'value', 'foo' => 'bar'];
        $property = CustomProperty::make('test_property')
            ->config($config);

        $this->assertEquals($config, $property->config);
    }

    #[Test]
    public function it_can_set_config_via_constructor()
    {
        $config = ['option' => 'value'];
        $property = new CustomProperty(
            'test_property',
            'Test Label',
            'App\\Livewire\\CustomComponent',
            $config
        );

        $this->assertEquals($config, $property->config);
    }

    #[Test]
    public function it_defaults_to_empty_config_array()
    {
        $property = CustomProperty::make('test_property');

        $this->assertEquals([], $property->config);
    }

    #[Test]
    public function it_can_set_default_value()
    {
        $property = CustomProperty::make('test_property')
            ->default('default_value');

        $this->assertEquals('default_value', $property->defaultValue);
    }

    #[Test]
    public function it_can_set_default_value_via_constructor()
    {
        $property = new CustomProperty(
            'test_property',
            'Test Label',
            null,
            null,
            'default_value'
        );

        $this->assertEquals('default_value', $property->defaultValue);
    }

    #[Test]
    public function it_converts_to_array_correctly()
    {
        $property = CustomProperty::make('test_property', 'Test Label')
            ->component('App\\Livewire\\CustomComponent')
            ->config(['key' => 'value'])
            ->default('default_value');

        $array = $property->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('test_property', $array['name']);
        $this->assertEquals('Test Label', $array['label']);
        $this->assertEquals('custom', $array['type']);
        $this->assertEquals('App\\Livewire\\CustomComponent', $array['component']);
        $this->assertEquals(['key' => 'value'], $array['config']);
        $this->assertEquals('default_value', $array['defaultValue']);
    }

    #[Test]
    public function it_includes_group_information_in_array()
    {
        $property = CustomProperty::make('test_property', 'Test Label')
            ->setGroup('content', 'Content Settings', 2, 'heroicon-o-document-text');

        $array = $property->toArray();

        $this->assertEquals('content', $array['group']);
        $this->assertEquals('Content Settings', $array['groupLabel']);
        $this->assertEquals(2, $array['groupColumns']);
        $this->assertEquals('heroicon-o-document-text', $array['groupIcon']);
    }

    #[Test]
    public function it_supports_fluent_interface()
    {
        $property = CustomProperty::make('test_property')
            ->label('New Label')
            ->component('App\\Livewire\\Component')
            ->config(['option' => 'value'])
            ->default('default')
            ->setGroup('group', 'Group Label');

        $this->assertInstanceOf(CustomProperty::class, $property);
        $this->assertEquals('New Label', $property->label);
        $this->assertEquals('App\\Livewire\\Component', $property->component);
        $this->assertEquals(['option' => 'value'], $property->config);
        $this->assertEquals('default', $property->defaultValue);
        $this->assertEquals('group', $property->group);
    }

    #[Test]
    public function it_uses_name_as_default_label()
    {
        $property = CustomProperty::make('test_property');

        $this->assertEquals('test_property', $property->label);
    }

    #[Test]
    public function it_handles_null_component()
    {
        $property = CustomProperty::make('test_property');

        $this->assertNull($property->component);

        $array = $property->toArray();
        $this->assertNull($array['component']);
    }
}
