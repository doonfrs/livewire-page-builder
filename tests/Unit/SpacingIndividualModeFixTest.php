<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties\MarginProperty;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class SpacingIndividualModeFixTest extends TestCase
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
                'm-3' => '3',
                'm-4' => '4',
                'm-6' => '6',
            ],
            'unit' => 'px',
        ];
    }

    /** @test */
    public function individual_mode_only_updates_specific_device_when_changed(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getMarginProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set to individual mode
        $component->set('mode', 'individual');

        // Set initial different values for each device
        $component->set('individualClassValues.mobile.bottom', 'm-1');
        $component->set('individualClassValues.tablet.bottom', 'm-2');
        $component->set('individualClassValues.desktop.bottom', 'm-4');

        // Values should remain different
        $component->assertSet('individualClassValues.mobile.bottom', 'm-1');
        $component->assertSet('individualClassValues.tablet.bottom', 'm-2');
        $component->assertSet('individualClassValues.desktop.bottom', 'm-4');

        // Now change mobile to 3 - should only affect mobile
        $component->set('individualClassValues.mobile.bottom', 'm-3');

        // Mobile should change to 3, others should stay the same
        $component->assertSet('individualClassValues.mobile.bottom', 'm-3');
        $component->assertSet('individualClassValues.tablet.bottom', 'm-2'); // Should NOT change
        $component->assertSet('individualClassValues.desktop.bottom', 'm-4'); // Should NOT change
    }

    /** @test */
    public function individual_mode_can_set_different_values_per_device(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getMarginProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set to individual mode
        $component->set('mode', 'individual');
        $component->set('activeDevice', 'mobile');

        // Set mobile margin
        $component->set('individualClassValues.mobile.bottom', 'm-2');
        $component->assertSet('individualClassValues.mobile.bottom', 'm-2');

        // Switch to tablet and set different value
        $component->set('activeDevice', 'tablet');
        $component->set('individualClassValues.tablet.bottom', 'm-4');
        $component->assertSet('individualClassValues.tablet.bottom', 'm-4');

        // Switch to desktop and set different value
        $component->set('activeDevice', 'desktop');
        $component->set('individualClassValues.desktop.bottom', 'm-6');
        $component->assertSet('individualClassValues.desktop.bottom', 'm-6');

        // All values should be different and preserved
        $component->assertSet('individualClassValues.mobile.bottom', 'm-2');
        $component->assertSet('individualClassValues.tablet.bottom', 'm-4');
        $component->assertSet('individualClassValues.desktop.bottom', 'm-6');

        // Switch back to mobile - should still be m-2
        $component->set('activeDevice', 'mobile');
        $component->assertSet('individualClassValues.mobile.bottom', 'm-2');
    }

    /** @test */
    public function individual_mode_custom_values_work_per_device(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getMarginProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Set to individual mode
        $component->set('mode', 'individual');

        // Set custom values for different devices
        $component->set('individualClassValues.mobile.bottom', 'custom');
        $component->set('individualCustomValues.mobile.bottom', '10');

        $component->set('individualClassValues.tablet.bottom', 'custom');
        $component->set('individualCustomValues.tablet.bottom', '20');

        $component->set('individualClassValues.desktop.bottom', 'custom');
        $component->set('individualCustomValues.desktop.bottom', '30');

        // All should maintain their custom values
        $component->assertSet('individualClassValues.mobile.bottom', 'custom');
        $component->assertSet('individualCustomValues.mobile.bottom', '10');

        $component->assertSet('individualClassValues.tablet.bottom', 'custom');
        $component->assertSet('individualCustomValues.tablet.bottom', '20');

        $component->assertSet('individualClassValues.desktop.bottom', 'custom');
        $component->assertSet('individualCustomValues.desktop.bottom', '30');
    }
}
