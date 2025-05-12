<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Component;

class ColorPickerComponent extends Component
{
    public $rowId = null;

    public $blockId = null;

    public $propertyName = '';

    public $currentValue = '';

    public $showModal = false;

    public $customColor = '';

    public $presetColors = [
        'gray' => [
            'gray-50', 'gray-100', 'gray-200', 'gray-300', 'gray-400',
            'gray-500', 'gray-600', 'gray-700', 'gray-800', 'gray-900',
        ],
        'red' => ['red-300', 'red-400', 'red-500', 'red-600', 'red-700'],
        'blue' => ['blue-300', 'blue-400', 'blue-500', 'blue-600', 'blue-700'],
        'green' => ['green-300', 'green-400', 'green-500', 'green-600', 'green-700'],
        'yellow' => ['yellow-300', 'yellow-400', 'yellow-500', 'yellow-600', 'yellow-700'],
        'pink' => ['pink-300', 'pink-400', 'pink-500', 'pink-600', 'pink-700'],
        'purple' => ['purple-300', 'purple-400', 'purple-500', 'purple-600', 'purple-700'],
        'indigo' => ['indigo-300', 'indigo-400', 'indigo-500', 'indigo-600', 'indigo-700'],
    ];

    public function mount($propertyName, $currentValue = null, $rowId = null, $blockId = null)
    {
        $this->propertyName = $propertyName;
        $this->currentValue = $currentValue ?? '';
        $this->rowId = $rowId;
        $this->blockId = $blockId;

        // Check if current value is a custom color (hex code)
        if (str_starts_with($this->currentValue, '#')) {
            $this->customColor = $this->currentValue;
        }
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function toggleModal()
    {
        $this->showModal = ! $this->showModal;
    }

    public function selectColor($color)
    {
        $this->currentValue = $color;
        $this->customColor = '';
        $this->updateProperty();
    }

    public function selectCustomColor()
    {
        if (! empty($this->customColor)) {
            $this->currentValue = $this->customColor;
            $this->updateProperty();
        }
    }

    public function clearColor()
    {
        $this->currentValue = '';
        $this->customColor = '';
        $this->updateProperty();
    }

    protected function updateProperty()
    {
        $this->dispatch('updateBlockProperty', $this->rowId, $this->blockId, $this->propertyName, $this->currentValue);
    }

    public function render()
    {
        return view('page-builder::livewire.builder.block-properties.color-picker');
    }
}
