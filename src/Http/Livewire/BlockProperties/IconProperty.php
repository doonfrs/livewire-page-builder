<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

use Livewire\Component;
use Trinavo\LivewirePageBuilder\Services\IconService;
use Trinavo\LivewirePageBuilder\Services\IconKeywords;

class IconProperty extends Component
{
    public $propertyName;

    public $currentValue;

    public $propertyLabel;

    public $propertyStyles;

    public $propertySets;

    public $defaultValue;

    public $rowId;

    public $blockId;

    public $showModal = false;

    public $searchQuery = '';

    public $selectedStyle = 'outline';

    public $selectedSet = 'heroicons';

    protected IconService $iconService;

    protected IconKeywords $iconKeywords;

    public function boot(IconService $iconService, IconKeywords $iconKeywords)
    {
        $this->iconService = $iconService;
        $this->iconKeywords = $iconKeywords;
    }

    public function mount()
    {
        // Set first available style as default
        if (! empty($this->propertyStyles)) {
            $this->selectedStyle = $this->propertyStyles[0];
        }

        // Set first available icon set as default
        if (! empty($this->propertySets)) {
            $this->selectedSet = $this->propertySets[0];
        }
    }

    public function render()
    {
        $icons = $this->getFilteredIcons();

        return view('page-builder::livewire.builder.block-properties.icon-property', [
            'icons' => $icons,
            'availableStyles' => $this->getAvailableStyles(),
            'availableSets' => $this->getAvailableSets(),
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

    public function updatedSelectedSet()
    {
        // Automatically refresh the view when icon set changes
        // Reset selected style to first available style for this icon set
        $styles = $this->getStylesForSet($this->selectedSet);
        if (! empty($styles)) {
            $this->selectedStyle = array_key_first($styles);
        }
    }

    private function getFilteredIcons(): array
    {
        // If there's a search query, use the enhanced keyword search
        if (! empty($this->searchQuery)) {
            $searchResults = $this->iconService->searchIcons(
                query: $this->searchQuery,
                style: null,
                sets: $this->propertySets ?? ['heroicons']
            );

            // Filter results to only include the currently selected set and configured styles
            $icons = [];
            if (isset($searchResults[$this->selectedSet])) {
                foreach ($this->propertyStyles as $style) {
                    if (isset($searchResults[$this->selectedSet][$style])) {
                        $icons[$style] = $searchResults[$this->selectedSet][$style];
                    } else {
                        $icons[$style] = [];
                    }
                }
            } else {
                // No results for selected set, return empty arrays for all styles
                foreach ($this->propertyStyles as $style) {
                    $icons[$style] = [];
                }
            }

            return $icons;
        }

        // No search query - return all icons for the selected set
        $allIconSets = $this->iconService->getIcons(sets: $this->propertySets ?? ['heroicons']);
        $icons = [];

        // Get icons for the currently selected set
        if (! isset($allIconSets[$this->selectedSet])) {
            return [];
        }

        $selectedSetIcons = $allIconSets[$this->selectedSet];

        foreach ($this->propertyStyles as $style) {
            if (! isset($selectedSetIcons[$style])) {
                $icons[$style] = [];
                continue;
            }

            $icons[$style] = $selectedSetIcons[$style];
        }

        return $icons;
    }

    private function getAvailableStyles(): array
    {
        return $this->getStylesForSet($this->selectedSet);
    }

    private function getStylesForSet(string $set): array
    {
        $styleLabels = [
            'outline' => 'Outline',
            'solid' => 'Solid',
            'mini' => 'Mini',
            'regular' => 'Regular',
            'fill' => 'Fill',
        ];

        $allIconSets = $this->iconService->getIcons(sets: $this->propertySets ?? ['heroicons']);

        if (! isset($allIconSets[$set])) {
            return [];
        }

        $availableStyles = array_keys($allIconSets[$set]);
        $styles = [];

        foreach ($availableStyles as $style) {
            if (in_array($style, $this->propertyStyles)) {
                $styles[$style] = $styleLabels[$style] ?? ucfirst($style);
            }
        }

        return $styles;
    }

    private function getAvailableSets(): array
    {
        $setLabels = [
            'heroicons' => 'Heroicons',
            'bootstrap' => 'Bootstrap Icons',
        ];

        $sets = [];
        foreach ($this->propertySets ?? ['heroicons'] as $set) {
            $sets[$set] = $setLabels[$set] ?? ucfirst($set);
        }

        return $sets;
    }
}
