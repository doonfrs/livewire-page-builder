<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

use Livewire\Component;

class PaddingProperty extends Component
{
    public $property;

    public $value;

    public $rowId;

    public $blockId;

    // Current state
    public $activeDevice = 'mobile';

    public $mode = 'unified'; // 'unified' or 'individual'

    // Unified values (when mode = unified) - per edge, applies to all devices
    public $unifiedClassValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];

    public $unifiedCustomValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];

    // Individual values (when mode = individual)
    public $individualClassValues = [
        'mobile' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
        'tablet' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
        'desktop' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
    ];

    public $individualCustomValues = [
        'mobile' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
        'tablet' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
        'desktop' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
    ];

    protected $devices = ['mobile', 'tablet', 'desktop'];

    protected $directions = ['top', 'right', 'bottom', 'left'];

    public function mount($property, $value = [], $rowId = null, $blockId = null)
    {
        $this->property = $property;
        $this->value = $value ?: [];
        $this->rowId = $rowId;
        $this->blockId = $blockId;

        $this->initializeFromCurrentValues();
    }

    protected function initializeFromCurrentValues()
    {
        // Load existing values from individual property fields
        if (! empty($this->value)) {
            $this->loadExistingValues();
        } else {
            // Default to unified padding mode
            $this->mode = 'unified';
        }
    }

    protected function loadExistingValues()
    {
        $hasIndividualValues = false;
        $unifiedValues = []; // Track values that could be unified

        foreach ($this->devices as $device) {
            foreach ($this->directions as $direction) {
                $paddingKey = $device.'Padding'.ucfirst($direction);

                if (! empty($this->value[$paddingKey])) {
                    $this->individualCustomValues[$device][$direction] = $this->value[$paddingKey];

                    // Track values for potential unified mode
                    if (!isset($unifiedValues[$direction])) {
                        $unifiedValues[$direction] = $this->value[$paddingKey];
                    }

                    $hasIndividualValues = true;
                }
            }
        }

        // Check if values are consistent across all devices (can be unified)
        if ($hasIndividualValues && $this->canBeUnified($unifiedValues)) {
            // Set unified mode and populate unified values
            $this->mode = 'unified';
            foreach ($this->directions as $direction) {
                if (isset($unifiedValues[$direction])) {
                    // Find the class that matches this value
                    $matchingClass = $this->findClassForValue($unifiedValues[$direction]);
                    if ($matchingClass) {
                        $this->unifiedClassValues[$direction] = $matchingClass;
                    } else {
                        // If no class matches, use custom
                        $this->unifiedClassValues[$direction] = 'custom';
                        $this->unifiedCustomValues[$direction] = $unifiedValues[$direction];
                    }
                }
            }
        } else if ($hasIndividualValues) {
            $this->mode = 'individual';
        } else {
            $this->mode = 'unified';
        }
    }

    protected function canBeUnified($unifiedValues)
    {
        // Check if all devices have the same values for each direction
        foreach ($this->directions as $direction) {
            if (!isset($unifiedValues[$direction])) continue;

            $expectedValue = $unifiedValues[$direction];
            foreach ($this->devices as $device) {
                $paddingKey = $device.'Padding'.ucfirst($direction);
                if (isset($this->value[$paddingKey]) && $this->value[$paddingKey] !== $expectedValue) {
                    return false; // Values differ across devices
                }
            }
        }
        return true;
    }

    protected function findClassForValue($value)
    {
        // Find which class produces this value
        foreach ($this->property['paddingClasses'] as $class => $label) {
            if ($class === '' || $class === 'custom') continue;

            $convertedValue = $this->convertUnifiedClassToIndividual($class, 'top'); // Use any direction for testing
            if ($convertedValue === $value) {
                return $class;
            }
        }
        return null;
    }

    public function updatedActiveDevice()
    {
        // Nothing special needed when switching devices
    }

    public function updatedMode()
    {
        \Log::info('PaddingProperty: Mode changed', [
            'new_mode' => $this->mode,
            'old_values' => [
                'unified' => $this->unifiedClassValues,
                'individual' => $this->individualClassValues
            ]
        ]);

        // Don't clear values when switching modes to preserve existing data
        // Only reset the display arrays, not the actual property values
    }

    protected function clearAllValues()
    {
        \Log::info('PaddingProperty: clearAllValues called - this should not update properties');

        $this->unifiedClassValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
        $this->unifiedCustomValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
        $this->individualClassValues = [
            'mobile' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
            'tablet' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
            'desktop' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
        ];
        $this->individualCustomValues = [
            'mobile' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
            'tablet' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
            'desktop' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
        ];

        // DO NOT call updateAllProperties() here - this was causing the cross-device contamination
        // $this->updateAllProperties();
    }

    public function updatedUnifiedClassValues()
    {
        if ($this->mode === 'unified') {
            $this->updateAllProperties();
        }
    }

    public function updatedUnifiedCustomValues()
    {
        if ($this->mode === 'unified') {
            $this->updateAllProperties();
        }
    }

    public function updatedIndividualClassValues($value, $key)
    {
        \Log::info('PaddingProperty: individualClassValues updated', [
            'key' => $key,
            'new_value' => $value,
            'mode' => $this->mode,
            'all_values_after_update' => $this->individualClassValues
        ]);

        if ($this->mode === 'individual') {
            // Parse the key to get device and direction (e.g., "mobile.bottom")
            $parts = explode('.', $key);
            if (count($parts) >= 2) {
                $device = $parts[0];
                $direction = $parts[1];
                $this->updateIndividualProperty($device, $direction);
            }
        }
    }

    public function updateIndividualValue($device, $direction, $value)
    {
        \Log::info('PaddingProperty: updateIndividualValue called', [
            'device' => $device,
            'direction' => $direction,
            'value' => $value,
            'before_update' => $this->individualClassValues
        ]);

        // Update the specific device/direction
        $this->individualClassValues[$device][$direction] = $value;

        \Log::info('PaddingProperty: after updating individualClassValues', [
            'after_update' => $this->individualClassValues
        ]);

        // Dispatch the property update
        $this->updateIndividualProperty($device, $direction);
    }

    // Add a more general method to catch any property update
    public function updated($propertyName, $value)
    {
        \Log::info('PaddingProperty: ANY property updated', [
            'property' => $propertyName,
            'value' => $value,
            'mode' => $this->mode
        ]);
    }

    public function updatedIndividualCustomValues($value, $key)
    {
        if ($this->mode === 'individual') {
            // Parse the key to get device and direction (e.g., "mobile.bottom")
            $parts = explode('.', $key);
            if (count($parts) >= 2) {
                $device = $parts[0];
                $direction = $parts[1];
                $this->updateIndividualProperty($device, $direction);
            }
        }
    }

    protected function updateAllProperties()
    {
        foreach ($this->devices as $device) {
            foreach ($this->directions as $direction) {
                $this->updateIndividualProperty($device, $direction);
            }
        }
    }

    protected function updateIndividualProperty($device, $direction)
    {
        $paddingPropertyName = $device.'Padding'.ucfirst($direction);
        $newValue = $this->calculateValueForProperty($device, $direction);

        $this->dispatch('updateBlockProperty', $this->rowId, $this->blockId, $paddingPropertyName, $newValue);
    }

    protected function calculateValueForProperty($device, $direction)
    {
        if ($this->mode === 'unified') {
            // In unified mode, use the value set for this direction across all devices
            $classValue = $this->unifiedClassValues[$direction] ?? '';
            if ($classValue === 'custom') {
                return $this->unifiedCustomValues[$direction] ?? '';
            } else {
                return $this->convertUnifiedClassToIndividual($classValue, $direction);
            }
        } else {
            // In individual mode, check if any value is set to 'custom' for this device/direction
            $classValue = $this->individualClassValues[$device][$direction] ?? '';
            if ($classValue === 'custom') {
                return $this->individualCustomValues[$device][$direction] ?? '';
            } else {
                return $this->convertUnifiedClassToIndividual($classValue, $direction);
            }
        }
    }

    protected function convertUnifiedClassToIndividual($unifiedClass, $direction)
    {
        if (! $unifiedClass) {
            return '';
        }

        // Convert unified classes like 'p-4' to individual values like '4'
        // Or classes like 'px-4' to individual values for horizontal directions

        // Handle unified classes like p-4
        if (preg_match('/^p-(.+)$/', $unifiedClass, $matches)) {
            return $matches[1]; // Return the numeric part
        }

        // Handle directional classes like px-4, py-4, pt-4, etc.
        $directionMap = [
            'top' => ['t', 'y'],
            'right' => ['r', 'x'],
            'bottom' => ['b', 'y'],
            'left' => ['l', 'x'],
        ];

        foreach ($directionMap[$direction] as $directionPrefix) {
            $pattern = '/^p'.$directionPrefix.'-(.+)$/';
            if (preg_match($pattern, $unifiedClass, $matches)) {
                return $matches[1];
            }
        }

        return '';
    }

    public function render()
    {
        return view('page-builder::livewire.builder.block-properties.padding-property');
    }
}
