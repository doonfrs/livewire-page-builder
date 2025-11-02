<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

use Livewire\Component;

class ColorPicker extends Component
{
    public $rowId = null;

    public $blockId = null;

    public $propertyName;

    public $propertyLabel;

    public $currentValue;

    public $showModal = false;

    public $customColor = '';

    public $opacity = 100;

    public $activeTab = 'theme';

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

    public $themeColors = [
        'base' => [
            'base-100', 'base-200', 'base-300', 'base-content',
        ],
        'primary' => [
            'primary', 'primary-content',
        ],
        'secondary' => [
            'secondary', 'secondary-content',
        ],
        'accent' => [
            'accent', 'accent-content',
        ],
        'neutral' => [
            'neutral', 'neutral-content',
        ],
        'info' => [
            'info', 'info-content',
        ],
        'success' => [
            'success', 'success-content',
        ],
        'warning' => [
            'warning', 'warning-content',
        ],
        'error' => [
            'error', 'error-content',
        ],
    ];

    public function mount()
    {
        if (str_starts_with($this->currentValue, '#')) {
            $this->customColor = $this->currentValue;
            $this->activeTab = 'custom';
            // Extract opacity from hex8 format (#RRGGBBAA)
            if (strlen($this->currentValue) === 9) {
                $alpha = hexdec(substr($this->currentValue, 7, 2));
                $this->opacity = round(($alpha / 255) * 100);
                $this->customColor = substr($this->currentValue, 0, 7);
            }
        } elseif (str_starts_with($this->currentValue, 'rgb')) {
            // Parse rgba format
            preg_match('/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/', $this->currentValue, $matches);
            if (count($matches) >= 4) {
                $r = str_pad(dechex($matches[1]), 2, '0', STR_PAD_LEFT);
                $g = str_pad(dechex($matches[2]), 2, '0', STR_PAD_LEFT);
                $b = str_pad(dechex($matches[3]), 2, '0', STR_PAD_LEFT);
                $this->customColor = "#{$r}{$g}{$b}";
                $this->opacity = isset($matches[4]) ? round($matches[4] * 100) : 100;
                $this->activeTab = 'custom';
            }
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

    public function setTab($tab)
    {
        $this->activeTab = $tab;
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
            // If opacity is less than 100, convert to rgba format
            if ($this->opacity < 100) {
                $hex = ltrim($this->customColor, '#');
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                $alpha = $this->opacity / 100;
                $this->currentValue = "rgba({$r}, {$g}, {$b}, {$alpha})";
            } else {
                $this->currentValue = $this->customColor;
            }
            $this->updateProperty();
        }
    }

    public function clearColor()
    {
        $this->currentValue = '';
        $this->customColor = '';
        $this->opacity = 100;
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
