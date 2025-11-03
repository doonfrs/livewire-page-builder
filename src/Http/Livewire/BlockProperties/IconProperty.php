<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

use Livewire\Component;
use Trinavo\LivewirePageBuilder\Services\IconService;

class IconProperty extends Component
{
    public $propertyName;

    public $currentValue;

    public $propertyLabel;

    public $propertyStyles;

    public $defaultValue;

    public $rowId;

    public $blockId;

    public $showModal = false;

    public $searchQuery = '';

    public $selectedStyle = 'outline';

    protected IconService $iconService;

    public function boot(IconService $iconService)
    {
        $this->iconService = $iconService;
    }

    public function mount()
    {
        // Set first available style as default
        if (! empty($this->propertyStyles)) {
            $this->selectedStyle = $this->propertyStyles[0];
        }
    }

    public function render()
    {
        $icons = $this->getFilteredIcons();

        return view('page-builder::livewire.builder.block-properties.icon-property', [
            'icons' => $icons,
            'availableStyles' => $this->getAvailableStyles(),
        ]);
    }

    public function selectIcon($iconComponent)
    {
        $this->currentValue = $iconComponent;
        $this->dispatch('updateBlockProperty', $this->rowId, $this->blockId, $this->propertyName, $iconComponent);
        $this->showModal = false;
        $this->searchQuery = '';
    }

    public function removeIcon()
    {
        $this->currentValue = null;
        $this->dispatch('updateBlockProperty', $this->rowId, $this->blockId, $this->propertyName, null);
    }

    public function openModal()
    {
        $this->showModal = true;
        $this->searchQuery = '';
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->searchQuery = '';
    }

    public function updatedSearchQuery()
    {
        // Automatically refresh the view when search query changes
    }

    public function updatedSelectedStyle()
    {
        // Automatically refresh the view when style changes
    }

    private function getFilteredIcons(): array
    {
        $allIcons = $this->iconService->getHeroicons();
        $icons = [];

        foreach ($this->propertyStyles as $style) {
            if (! isset($allIcons[$style])) {
                continue;
            }

            $styleIcons = $allIcons[$style];

            if (! empty($this->searchQuery)) {
                $query = strtolower($this->searchQuery);
                $styleIcons = array_filter($styleIcons, function ($icon) use ($query) {
                    return str_contains(strtolower($icon['name']), $query) ||
                           str_contains(strtolower($icon['searchTerms']), $query);
                });
            }

            $icons[$style] = array_values($styleIcons);
        }

        return $icons;
    }

    private function getAvailableStyles(): array
    {
        $styleLabels = [
            'outline' => 'Outline',
            'solid' => 'Solid',
            'mini' => 'Mini',
        ];

        $styles = [];
        foreach ($this->propertyStyles as $style) {
            $styles[$style] = $styleLabels[$style] ?? ucfirst($style);
        }

        return $styles;
    }
}
