<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

use Livewire\Component;

class MarginProperty extends Component
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

    // Individual values - using separate properties to avoid Livewire array reference issues
    public $mobileClassValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
    public $tabletClassValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
    public $desktopClassValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];

    public $mobileCustomValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
    public $tabletCustomValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
    public $desktopCustomValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];

    protected $devices = ['mobile', 'tablet', 'desktop'];

    protected $directions = ['top', 'right', 'bottom', 'left'];

    // Helper methods to get/set device-specific values
    protected function getDeviceClassValues($device)
    {
        switch ($device) {
            case 'mobile': return $this->mobileClassValues;
            case 'tablet': return $this->tabletClassValues;
            case 'desktop': return $this->desktopClassValues;
            default: return ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
        }
    }

    protected function setDeviceClassValue($device, $direction, $value)
    {
        \Log::info('MarginProperty: setDeviceClassValue', [
            'device' => $device,
            'direction' => $direction,
            'value' => $value
        ]);

        switch ($device) {
            case 'mobile':
                $this->mobileClassValues[$direction] = $value;
                break;
            case 'tablet':
                $this->tabletClassValues[$direction] = $value;
                break;
            case 'desktop':
                $this->desktopClassValues[$direction] = $value;
                break;
        }

        \Log::info('MarginProperty: After setDeviceClassValue', [
            'mobileClassValues' => $this->mobileClassValues,
            'tabletClassValues' => $this->tabletClassValues,
            'desktopClassValues' => $this->desktopClassValues
        ]);
    }

    protected function getDeviceCustomValues($device)
    {
        switch ($device) {
            case 'mobile': return $this->mobileCustomValues;
            case 'tablet': return $this->tabletCustomValues;
            case 'desktop': return $this->desktopCustomValues;
            default: return ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
        }
    }

    protected function setDeviceCustomValue($device, $direction, $value)
    {
        switch ($device) {
            case 'mobile':
                $this->mobileCustomValues[$direction] = $value;
                break;
            case 'tablet':
                $this->tabletCustomValues[$direction] = $value;
                break;
            case 'desktop':
                $this->desktopCustomValues[$direction] = $value;
                break;
        }
    }

    public function mount($property, $value = [], $rowId = null, $blockId = null)
    {
        $this->property = $property;
        $this->value = $value ?: [];
        $this->rowId = $rowId;
        $this->blockId = $blockId;

        \Log::info('MarginProperty: Component mounted', [
            'input_values' => $this->value,
            'rowId' => $this->rowId,
            'blockId' => $this->blockId
        ]);

        $this->initializeFromCurrentValues();

        \Log::info('MarginProperty: After initialization', [
            'mode' => $this->mode,
            'mobileClassValues' => $this->mobileClassValues,
            'tabletClassValues' => $this->tabletClassValues,
            'desktopClassValues' => $this->desktopClassValues,
            'activeDevice' => $this->activeDevice
        ]);
    }

    protected function initializeFromCurrentValues()
    {
        // Load existing values from individual property fields
        if (! empty($this->value)) {
            $this->loadExistingValues();
        } else {
            // Default to unified margin mode
            $this->mode = 'unified';
        }
    }

    protected function loadExistingValues()
    {
        \Log::info('MarginProperty: loadExistingValues called', [
            'input_values' => $this->value
        ]);

        $hasIndividualValues = false;
        $unifiedValues = []; // Track values that could be unified

        // Reset separate device properties to ensure clean state
        $this->mobileClassValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
        $this->tabletClassValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
        $this->desktopClassValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
        $this->mobileCustomValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
        $this->tabletCustomValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
        $this->desktopCustomValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];

        foreach ($this->devices as $device) {
            foreach ($this->directions as $direction) {
                $marginKey = $device.'Margin'.ucfirst($direction);

                if (! empty($this->value[$marginKey])) {
                    \Log::info('MarginProperty: Loading existing value', [
                        'device' => $device,
                        'direction' => $direction,
                        'key' => $marginKey,
                        'value' => $this->value[$marginKey]
                    ]);

                    // Set custom value for this device
                    $this->setDeviceCustomValue($device, $direction, $this->value[$marginKey]);

                    // Find the class that matches this value and set it
                    $matchingClass = $this->findClassForValue($this->value[$marginKey]);
                    if ($matchingClass) {
                        $this->setDeviceClassValue($device, $direction, $matchingClass);
                    } else {
                        $this->setDeviceClassValue($device, $direction, 'custom');
                    }

                    // Track values for potential unified mode
                    if (!isset($unifiedValues[$direction])) {
                        $unifiedValues[$direction] = $this->value[$marginKey];
                    }

                    $hasIndividualValues = true;
                }
            }
        }

        \Log::info('MarginProperty: After loading values', [
            'mobileClassValues' => $this->mobileClassValues,
            'tabletClassValues' => $this->tabletClassValues,
            'desktopClassValues' => $this->desktopClassValues,
            'hasIndividualValues' => $hasIndividualValues
        ]);

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

        \Log::info('MarginProperty: Final mode and values', [
            'mode' => $this->mode,
            'mobile' => $this->mobileClassValues,
            'tablet' => $this->tabletClassValues,
            'desktop' => $this->desktopClassValues
        ]);
    }

    protected function canBeUnified($unifiedValues)
    {
        // Check if all devices have the same values for each direction
        foreach ($this->directions as $direction) {
            if (!isset($unifiedValues[$direction])) continue;

            $expectedValue = $unifiedValues[$direction];
            foreach ($this->devices as $device) {
                $marginKey = $device.'Margin'.ucfirst($direction);
                if (isset($this->value[$marginKey]) && $this->value[$marginKey] !== $expectedValue) {
                    return false; // Values differ across devices
                }
            }
        }
        return true;
    }

    protected function findClassForValue($value)
    {
        // Find which class produces this value
        foreach ($this->property['marginClasses'] as $class => $label) {
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
        \Log::info('MarginProperty: Active device changed', [
            'new_device' => $this->activeDevice,
            'mobile_values' => $this->mobileClassValues,
            'tablet_values' => $this->tabletClassValues,
            'desktop_values' => $this->desktopClassValues,
            'specific_device_values' => $this->getDeviceClassValues($this->activeDevice)
        ]);
    }

    public function updatedMode()
    {
        \Log::info('MarginProperty: Mode changed', [
            'new_mode' => $this->mode,
            'old_values' => [
                'unified' => $this->unifiedClassValues,
                'mobile' => $this->mobileClassValues,
                'tablet' => $this->tabletClassValues,
                'desktop' => $this->desktopClassValues
            ]
        ]);

        // Don't clear values when switching modes to preserve existing data
        // Only reset the display arrays, not the actual property values
    }

    protected function clearAllValues()
    {
        \Log::info('MarginProperty: clearAllValues called - this should not update properties');

        $this->unifiedClassValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
        $this->unifiedCustomValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];

        // Clear separate device properties
        $this->mobileClassValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
        $this->tabletClassValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
        $this->desktopClassValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
        $this->mobileCustomValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
        $this->tabletCustomValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];
        $this->desktopCustomValues = ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''];

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
        \Log::error('MarginProperty: individualClassValues auto-updated - THIS SHOULD NOT HAPPEN', [
            'key' => $key,
            'new_value' => $value,
            'mode' => $this->mode,
            'message' => 'This method should not be called since individualClassValues property was removed',
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
        ]);

        // This method should NOT be called since we removed wire:model
        // If it's being called, there's still some automatic binding happening
        return;
    }

    // Add a more general method to catch any property update
    public function updated($propertyName, $value)
    {
        \Log::info('MarginProperty: ANY property updated', [
            'property' => $propertyName,
            'value' => $value,
            'mode' => $this->mode
        ]);
    }

    public function updateIndividualValue($device, $direction, $value)
    {
        \Log::info('MarginProperty: updateIndividualValue called', [
            'device' => $device,
            'direction' => $direction,
            'value' => $value,
            'before_mobile' => $this->mobileClassValues,
            'before_tablet' => $this->tabletClassValues,
            'before_desktop' => $this->desktopClassValues,
            'current_mode' => $this->mode,
            'active_device' => $this->activeDevice
        ]);

        // Ensure we're in individual mode
        if ($this->mode !== 'individual') {
            \Log::warning('MarginProperty: updateIndividualValue called but not in individual mode', [
                'current_mode' => $this->mode
            ]);
            return;
        }

        // Update ONLY the specific device/direction using separate properties
        $this->setDeviceClassValue($device, $direction, $value);

        // Dispatch the property update ONLY for this specific device/direction
        $this->updateIndividualProperty($device, $direction);
    }

    // Simple test method
    public function testMethod()
    {
        \Log::info('MarginProperty: testMethod called - Livewire methods are working!');
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
        $marginPropertyName = $device.'Margin'.ucfirst($direction);
        $newValue = $this->calculateValueForProperty($device, $direction);

        $this->dispatch('updateBlockProperty', $this->rowId, $this->blockId, $marginPropertyName, $newValue);
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
            // In individual mode, use the separate device properties
            $deviceClassValues = $this->getDeviceClassValues($device);
            $deviceCustomValues = $this->getDeviceCustomValues($device);

            $classValue = $deviceClassValues[$direction] ?? '';
            if ($classValue === 'custom') {
                return $deviceCustomValues[$direction] ?? '';
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

        // Convert unified classes like 'm-4' to individual values like '4'
        // Or classes like 'mx-4' to individual values for horizontal directions

        // Handle unified classes like m-4
        if (preg_match('/^m-(.+)$/', $unifiedClass, $matches)) {
            return $matches[1]; // Return the numeric part
        }

        // Handle directional classes like mx-4, my-4, mt-4, etc.
        $directionMap = [
            'top' => ['t', 'y'],
            'right' => ['r', 'x'],
            'bottom' => ['b', 'y'],
            'left' => ['l', 'x'],
        ];

        foreach ($directionMap[$direction] as $directionPrefix) {
            $pattern = '/^m'.$directionPrefix.'-(.+)$/';
            if (preg_match($pattern, $unifiedClass, $matches)) {
                return $matches[1];
            }
        }

        return '';
    }

    public function render()
    {
        return view('page-builder::livewire.builder.block-properties.margin-property');
    }
}
