<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

use Livewire\Component;

class FlexibleSizeProperty extends Component
{
    public $property;
    public $value;
    public $rowId;
    public $blockId;
    public $mode = 'class'; // 'class' or 'custom'
    public $customValue = '';
    public $selectedClass = '';
    public $prefix = 'w'; // Will be set based on property name

    public function mount($property, $value = null, $rowId = null, $blockId = null)
    {
        $this->property = $property;
        $this->value = $value;
        $this->rowId = $rowId;
        $this->blockId = $blockId;

        // Determine the Tailwind prefix based on property name
        $this->prefix = $this->determineTailwindPrefix($property['name']);

        // Initialize to empty state
        $this->selectedClass = '';
        $this->customValue = '';

        // Determine mode based on current value
        if ($value && $value !== '') {
            // Check if value is a predefined class
            if (isset($this->property['classes']) && array_key_exists($value, $this->property['classes'])) {
                $this->mode = 'class';
                $this->selectedClass = $value;
            } else {
                // It's a custom value (arbitrary value or numeric)
                $this->mode = 'custom';

                // Extract numeric value from Tailwind arbitrary value syntax
                // e.g., "w-[100px]" -> "100", "h-[200px]" -> "200"
                if (preg_match('/\[(\d+(?:\.\d+)?)(?:px|rem|em|%)?\]/', $value, $matches)) {
                    $this->customValue = $matches[1];
                } elseif (is_numeric($value)) {
                    // Direct numeric value (backward compatibility)
                    $this->customValue = $value;
                } else {
                    // Try to extract any numeric value as fallback
                    $this->customValue = preg_replace('/[^0-9.]/', '', $value);
                    if (!$this->customValue) {
                        // If we couldn't extract a value, it might be an unrecognized class
                        // Default to class mode with the first option
                        $this->mode = 'class';
                        if (!empty($this->property['classes'])) {
                            $this->selectedClass = array_key_first($this->property['classes']);
                        }
                    }
                }
            }
        } else {
            // No value set, default to class mode
            $this->mode = 'class';
            // Don't auto-select a class, let user choose
        }
    }

    protected function determineTailwindPrefix($propertyName)
    {
        // Determine prefix based on property name
        if (str_contains(strtolower($propertyName), 'width')) {
            return 'w';
        } elseif (str_contains(strtolower($propertyName), 'minheight')) {
            return 'min-h';
        } elseif (str_contains(strtolower($propertyName), 'height')) {
            return 'h';
        }

        return 'w'; // Default to width
    }

    public function updatedMode()
    {
        if ($this->mode === 'class') {
            $this->customValue = '';
            // If switching to class mode and no class is selected, don't update the value yet
            if ($this->selectedClass) {
                $this->updateValue($this->selectedClass);
            }
        } else {
            $this->selectedClass = '';
            // If switching to custom mode and no custom value, don't update yet
            if ($this->customValue) {
                $this->updateValue($this->generateTailwindArbitraryValue());
            }
        }
    }

    public function updatedSelectedClass()
    {
        if ($this->mode === 'class' && $this->selectedClass) {
            $this->updateValue($this->selectedClass);
        }
    }

    public function updatedCustomValue()
    {
        if ($this->mode === 'custom') {
            // Allow clearing the value
            if ($this->customValue === '' || $this->customValue === null) {
                $this->updateValue('');
            } else {
                $this->updateValue($this->generateTailwindArbitraryValue());
            }
        }
    }

    protected function generateTailwindArbitraryValue()
    {
        if (!$this->customValue) {
            return '';
        }

        // Generate Tailwind arbitrary value syntax
        // e.g., w-[100px], h-[200px], min-h-[300px]
        return $this->prefix . '-[' . $this->customValue . $this->property['unit'] . ']';
    }

    protected function updateValue($newValue)
    {
        $this->value = $newValue;
        $this->dispatch('updateBlockProperty', $this->rowId, $this->blockId, $this->property['name'], $newValue);
    }

    public function render()
    {
        return view('page-builder::livewire.builder.block-properties.flexible-size-property');
    }
}