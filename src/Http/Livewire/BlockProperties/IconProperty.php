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

    public $selectedSet = 'all';

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

        // Default to 'all' to show icons from all sets
        $this->selectedSet = 'all';
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
        if ($this->selectedSet !== 'all') {
            $styles = $this->getStylesForSet($this->selectedSet);
            if (! empty($styles)) {
                $this->selectedStyle = array_key_first($styles);
            }
        } else {
            // For "all", reset to first configured style
            if (! empty($this->propertyStyles)) {
                $this->selectedStyle = $this->propertyStyles[0];
            }
        }
    }

    private function getFilteredIcons(): array
    {
        $allIconSets = $this->iconService->getIcons(sets: $this->propertySets ?? ['heroicons']);

        // If there's a search query, use the enhanced keyword search
        if (! empty($this->searchQuery)) {
            $searchResults = $this->iconService->searchIcons(
                query: $this->searchQuery,
                style: null,
                sets: $this->propertySets ?? ['heroicons']
            );

            $icons = [];

            if ($this->selectedSet === 'all') {
                // Combine results from all sets
                foreach ($this->propertyStyles as $style) {
                    $combinedIcons = [];
                    foreach ($this->propertySets ?? ['heroicons'] as $set) {
                        if (isset($searchResults[$set][$style])) {
                            $combinedIcons = array_merge($combinedIcons, $searchResults[$set][$style]);
                        }
                    }
                    $icons[$style] = $combinedIcons;
                }
            } else {
                // Filter results to only include the currently selected set and configured styles
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
            }

            return $icons;
        }

        // No search query - return all icons
        $icons = [];

        if ($this->selectedSet === 'all') {
            // Combine icons from all sets
            foreach ($this->propertyStyles as $style) {
                $combinedIcons = [];
                foreach ($this->propertySets ?? ['heroicons'] as $set) {
                    if (isset($allIconSets[$set][$style])) {
                        $combinedIcons = array_merge($combinedIcons, $allIconSets[$set][$style]);
                    }
                }
                $icons[$style] = $combinedIcons;
            }
        } else {
            // Get icons for the currently selected set
            if (! isset($allIconSets[$this->selectedSet])) {
                foreach ($this->propertyStyles as $style) {
                    $icons[$style] = [];
                }
                return $icons;
            }

            $selectedSetIcons = $allIconSets[$this->selectedSet];

            foreach ($this->propertyStyles as $style) {
                if (! isset($selectedSetIcons[$style])) {
                    $icons[$style] = [];
                    continue;
                }

                $icons[$style] = $selectedSetIcons[$style];
            }
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

        // For "all", collect all unique styles from all sets
        if ($set === 'all') {
            $allAvailableStyles = [];
            foreach ($this->propertySets ?? ['heroicons'] as $iconSet) {
                if (isset($allIconSets[$iconSet])) {
                    $allAvailableStyles = array_merge($allAvailableStyles, array_keys($allIconSets[$iconSet]));
                }
            }
            $allAvailableStyles = array_unique($allAvailableStyles);

            $styles = [];
            foreach ($allAvailableStyles as $style) {
                if (in_array($style, $this->propertyStyles)) {
                    $styles[$style] = $styleLabels[$style] ?? ucfirst($style);
                }
            }

            return $styles;
        }

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
            'all' => 'All Icons',
            'heroicons' => 'Heroicons',
            'bootstrap' => 'Bootstrap Icons',
        ];

        $sets = [];

        // Add "All" option first if there are multiple sets
        if (count($this->propertySets ?? []) > 1) {
            $sets['all'] = $setLabels['all'];
        }

        // Add individual sets
        foreach ($this->propertySets ?? ['heroicons'] as $set) {
            $sets[$set] = $setLabels[$set] ?? ucfirst($set);
        }

        return $sets;
    }
}
