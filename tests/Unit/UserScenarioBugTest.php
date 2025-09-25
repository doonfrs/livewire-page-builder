<?php

namespace Trinavo\LivewirePageBuilder\Tests\Unit;

use Livewire\Livewire;
use Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties\MarginProperty;
use Trinavo\LivewirePageBuilder\Tests\TestCase;

class UserScenarioBugTest extends TestCase
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
                'm-6' => '6',
            ],
            'unit' => 'px',
        ];
    }

    /** @test */
    public function exact_user_scenario_mobile_2_tablet_4_desktop_6(): void
    {
        $component = Livewire::test(MarginProperty::class, [
            'property' => $this->getMarginProperty(),
            'value' => [],
            'rowId' => 'test-row',
            'blockId' => 'test-block',
        ]);

        // Step 1: Switch to Per Device mode
        $component->set('mode', 'individual');

        echo "After setting mode to individual:\n";
        echo "Mode: " . $component->get('mode') . "\n";
        echo "individualClassValues: " . json_encode($component->get('individualClassValues')) . "\n";

        // Step 2: Set mobile to 2
        $component->set('activeDevice', 'mobile');
        $component->set('individualClassValues.mobile.bottom', 'm-2');

        echo "\nAfter setting mobile to m-2:\n";
        echo "Mobile bottom: " . $component->get('individualClassValues.mobile.bottom') . "\n";
        echo "Tablet bottom: " . $component->get('individualClassValues.tablet.bottom') . "\n";
        echo "Desktop bottom: " . $component->get('individualClassValues.desktop.bottom') . "\n";

        // Step 3: Set tablet to 4
        $component->set('activeDevice', 'tablet');
        $component->set('individualClassValues.tablet.bottom', 'm-4');

        echo "\nAfter setting tablet to m-4:\n";
        echo "Mobile bottom: " . $component->get('individualClassValues.mobile.bottom') . "\n";
        echo "Tablet bottom: " . $component->get('individualClassValues.tablet.bottom') . "\n";
        echo "Desktop bottom: " . $component->get('individualClassValues.desktop.bottom') . "\n";

        // Step 4: Set desktop to 6
        $component->set('activeDevice', 'desktop');
        $component->set('individualClassValues.desktop.bottom', 'm-6');

        echo "\nAfter setting desktop to m-6:\n";
        echo "Mobile bottom: " . $component->get('individualClassValues.mobile.bottom') . "\n";
        echo "Tablet bottom: " . $component->get('individualClassValues.tablet.bottom') . "\n";
        echo "Desktop bottom: " . $component->get('individualClassValues.desktop.bottom') . "\n";

        // Step 5: Go back to mobile - should still be 2, not 6
        $component->set('activeDevice', 'mobile');

        echo "\nAfter switching back to mobile:\n";
        echo "Mobile bottom: " . $component->get('individualClassValues.mobile.bottom') . "\n";
        echo "Tablet bottom: " . $component->get('individualClassValues.tablet.bottom') . "\n";
        echo "Desktop bottom: " . $component->get('individualClassValues.desktop.bottom') . "\n";

        // This should pass - mobile should still be m-2, not m-6
        $this->assertEquals('m-2', $component->get('individualClassValues.mobile.bottom'));
        $this->assertEquals('m-4', $component->get('individualClassValues.tablet.bottom'));
        $this->assertEquals('m-6', $component->get('individualClassValues.desktop.bottom'));
    }
}