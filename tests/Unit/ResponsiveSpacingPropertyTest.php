<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties\ResponsiveSpacingProperty as ResponsiveSpacingPropertyComponent;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Support\Properties\ResponsiveSpacingProperty as ResponsiveSpacingPropertyDefinition;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class ResponsiveSpacingPropertyTest extends TestCase
{
    /** @test */
    public function updating_all_mode_dispatches_events_for_each_device(): void
    {
        $property = $this->makeMarginProperty();

        $component = Livewire::test(ResponsiveSpacingPropertyComponent::class, [
            'property' => $property,
            'values' => $property['values'],
            'rowId' => 'row-1',
            'blockId' => 'block-1',
        ]);

        $component->assertSet('mode', 'all');

        $component->set('values.all.top', '20');

        $component->assertSet('values.desktop.top', '20');
        $component->assertSet('values.tablet.top', '20');
        $component->assertSet('values.mobile.top', '20');

        $dispatches = collect($component->effects['dispatches'] ?? [])
            ->where('name', 'updateBlockProperty');

        $this->assertCount(3, $dispatches);

        foreach (['desktop', 'tablet', 'mobile'] as $device) {
            $expectedProperty = $property['fields'][$device]['top'];

            $this->assertTrue(
                $dispatches->contains(function (array $dispatch) use ($expectedProperty) {
                    [$rowId, $blockId, $propertyName, $value] = array_values($dispatch['params']);

                    return $rowId === 'row-1'
                        && $blockId === 'block-1'
                        && $propertyName === $expectedProperty
                        && $value === '20';
                }),
                sprintf('Failed asserting that dispatch for %s top was emitted.', $device)
            );
        }
    }

    /** @test */
    public function updating_single_device_only_dispatches_for_that_device(): void
    {
        $property = $this->makeMarginProperty([
            'tablet' => [
                'top' => 5,
            ],
        ]);

        $initialValues = $property['values'];

        $component = Livewire::test(ResponsiveSpacingPropertyComponent::class, [
            'property' => $property,
            'values' => $initialValues,
            'rowId' => 'row-2',
            'blockId' => 'block-2',
        ]);

        $component->assertSet('mode', 'per-device');
        $component->set('activeDevice', 'tablet');

        $component->set('values.tablet.top', '18');

        $component->assertSet('values.tablet.top', '18');
        $this->assertEquals(0, $component->get('values.desktop.top'));
        $this->assertEquals(0, $component->get('values.mobile.top'));

        $dispatches = collect($component->effects['dispatches'] ?? [])
            ->where('name', 'updateBlockProperty');

        $this->assertCount(1, $dispatches);

        [$rowId, $blockId, $propertyName, $value] = array_values($dispatches->first()['params']);

        $this->assertSame('row-2', $rowId);
        $this->assertSame('block-2', $blockId);
        $this->assertSame($property['fields']['tablet']['top'], $propertyName);
        $this->assertSame('18', $value);
    }

    /** @test */
    public function page_builder_service_resets_higher_margin_breakpoints_to_zero(): void
    {
        $service = app(PageBuilderService::class);

        $classes = $service->getCssClassesFromProperties([
            'mobileMarginTop' => 0,
            'tabletMarginTop' => 20,
            'desktopMarginTop' => 0,
        ]);

        $this->assertStringContainsString('@3xl:mt-20', $classes);
        $this->assertStringContainsString('@7xl:mt-0', $classes);
    }

    /** @test */
    public function page_builder_service_resets_higher_padding_breakpoints_to_zero(): void
    {
        $service = app(PageBuilderService::class);

        $classes = $service->getCssClassesFromProperties([
            'mobilePaddingTop' => 12,
            'tabletPaddingTop' => 0,
            'desktopPaddingTop' => 0,
        ]);

        $this->assertStringContainsString('pt-12', $classes);
        $this->assertStringContainsString('@3xl:pt-0', $classes);
        $this->assertStringNotContainsString('@7xl:pt-0', $classes);
    }

    private function makeMarginProperty(array $overrides = []): array
    {
        $defaults = [
            'desktop' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
            'tablet' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
            'mobile' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
        ];

        $values = array_replace_recursive($defaults, $overrides);

        return ResponsiveSpacingPropertyDefinition::make('margin', 'Margin', $values)->toArray();
    }
}
