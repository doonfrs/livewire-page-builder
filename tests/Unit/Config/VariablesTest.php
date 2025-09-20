<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit\Config;

use PHPUnit\Framework\TestCase;
use Trinavo\LivewirePageBuilder\Config\Variables;

class VariablesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clear variables before each test
        Variables::clear();
    }

    protected function tearDown(): void
    {
        // Clear variables after each test
        Variables::clear();

        parent::tearDown();
    }

    /** @test */
    public function it_can_register_and_get_a_variable(): void
    {
        Variables::register('test_var', 'test_value');

        $this->assertEquals('test_value', Variables::get('test_var'));
    }

    /** @test */
    public function it_can_register_multiple_variables(): void
    {
        $variables = [
            'var1' => 'value1',
            'var2' => 'value2',
            'var3' => 'value3',
        ];

        Variables::registerMany($variables);

        $this->assertEquals('value1', Variables::get('var1'));
        $this->assertEquals('value2', Variables::get('var2'));
        $this->assertEquals('value3', Variables::get('var3'));
    }

    /** @test */
    public function it_returns_default_value_for_non_existent_variable(): void
    {
        $this->assertNull(Variables::get('non_existent'));
        $this->assertEquals('default', Variables::get('non_existent', 'default'));
    }

    /** @test */
    public function it_can_handle_callable_variables(): void
    {
        Variables::register('callable_var', fn () => 'dynamic_value');

        $this->assertEquals('dynamic_value', Variables::get('callable_var'));
    }

    /** @test */
    public function it_can_check_if_variable_exists(): void
    {
        Variables::register('existing_var', 'value');

        $this->assertTrue(Variables::has('existing_var'));
        $this->assertFalse(Variables::has('non_existent_var'));
    }

    /** @test */
    public function it_can_remove_a_variable(): void
    {
        Variables::register('temp_var', 'temp_value');
        $this->assertTrue(Variables::has('temp_var'));

        Variables::remove('temp_var');
        $this->assertFalse(Variables::has('temp_var'));
    }

    /** @test */
    public function it_can_get_all_variables(): void
    {
        Variables::registerMany([
            'static_var' => 'static_value',
            'callable_var' => fn () => 'callable_value',
        ]);

        $all = Variables::all();

        $this->assertEquals([
            'static_var' => 'static_value',
            'callable_var' => 'callable_value',
        ], $all);
    }

    /** @test */
    public function it_can_clear_all_variables(): void
    {
        Variables::registerMany([
            'var1' => 'value1',
            'var2' => 'value2',
        ]);

        $this->assertCount(2, Variables::all());

        Variables::clear();

        $this->assertCount(0, Variables::all());
    }

    /** @test */
    public function callable_variables_are_executed_each_time(): void
    {
        $counter = 0;
        Variables::register('counter', function () use (&$counter) {
            return ++$counter;
        });

        $this->assertEquals(1, Variables::get('counter'));
        $this->assertEquals(2, Variables::get('counter'));
        $this->assertEquals(3, Variables::get('counter'));
    }
}