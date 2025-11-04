<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

use Livewire\Component;
use Trinavo\LivewirePageBuilder\Services\IconKeywords;
use Trinavo\LivewirePageBuilder\Services\IconService;

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

    public $currentPage = 1;

    public $perPage = 60;

    protected IconService $iconService;

    protected IconKeywords $iconKeywords;

    private ?array $cachedIconSets = null;

    private ?int $totalIcons = null;

    public function boot(IconService $iconService, IconKeywords $iconKeywords)
    {
        $this->iconService = $iconService;
        $this->iconKeywords = $iconKeywords;
    }

    private function getAllIconSets(): array
    {
        if ($this->cachedIconSets === null) {
            $this->cachedIconSets = $this->iconService->getIcons(sets: $this->propertySets ?? ['heroicons']);
        }

        return $this->cachedIconSets;
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
        // Only load icons when modal is open to avoid slowing down initial render
        $icons = [];
        $availableStyles = [];
        $availableSets = [];
        $totalPages = 0;
        $showing = ['from' => 0, 'to' => 0, 'total' => 0];

        if ($this->showModal) {
            $result = $this->getFilteredIcons();
            $icons = $result['icons'];
            $totalPages = $result['totalPages'];
            $showing = $result['showing'];
            $availableStyles = $this->getAvailableStyles();
            $availableSets = $this->getAvailableSets();
        }

        return view('page-builder::livewire.builder.block-properties.icon-property', [
            'icons' => $icons,
            'availableStyles' => $availableStyles,
            'availableSets' => $availableSets,
            'currentPage' => $this->currentPage,
            'totalPages' => $totalPages,
            'showing' => $showing,
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
        $this->currentPage = 1;
    }

    public function updatedSearchQuery()
    {
        // Reset to first page when search changes
        $this->currentPage = 1;
    }

    public function updatedSelectedStyle()
    {
        // Reset to first page when style changes
        $this->currentPage = 1;
    }

    public function updatedSelectedSet()
    {
        // Reset to first page when icon set changes
        $this->currentPage = 1;

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

    public function nextPage()
    {
        $this->currentPage++;
    }

    public function previousPage()
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
        }
    }

    public function goToPage($page)
    {
        $this->currentPage = max(1, (int) $page);
    }

    private function getFilteredIcons(): array
    {
        $allIconSets = $this->getAllIconSets();
        $allIconsForStyle = $this->getAllIconsForStyle($allIconSets);

        // Get icons for the selected style
        $iconsForCurrentStyle = $allIconsForStyle[$this->selectedStyle] ?? [];
        $total = count($iconsForCurrentStyle);

        // Calculate pagination
        $totalPages = $total > 0 ? (int) ceil($total / $this->perPage) : 0;
        $offset = ($this->currentPage - 1) * $this->perPage;

        // Ensure current page doesn't exceed total pages
        if ($this->currentPage > $totalPages && $totalPages > 0) {
            $this->currentPage = $totalPages;
            $offset = ($this->currentPage - 1) * $this->perPage;
        }

        // Slice for current page
        $paginatedIcons = array_slice($iconsForCurrentStyle, $offset, $this->perPage);

        // Calculate showing info
        $from = $total > 0 ? $offset + 1 : 0;
        $to = min($offset + $this->perPage, $total);

        return [
            'icons' => [$this->selectedStyle => $paginatedIcons],
            'totalPages' => $totalPages,
            'showing' => [
                'from' => $from,
                'to' => $to,
                'total' => $total,
            ],
        ];
    }

    private function getAllIconsForStyle(array $allIconSets): array
    {
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
                // When "All" is selected, we need to intelligently map styles between icon sets
                $styleMapping = [
                    'outline' => ['outline', 'regular'], // Heroicons outline ≈ Bootstrap regular
                    'solid' => ['solid', 'fill'],         // Heroicons solid ≈ Bootstrap fill
                    'mini' => ['mini', 'regular'],        // Heroicons mini ≈ Bootstrap regular
                    'regular' => ['regular', 'outline'],  // Bootstrap regular ≈ Heroicons outline
                    'fill' => ['fill', 'solid'],          // Bootstrap fill ≈ Heroicons solid
                ];

                foreach ($this->propertyStyles as $style) {
                    $combinedIcons = [];
                    $stylesToCheck = $styleMapping[$style] ?? [$style];

                    foreach ($this->propertySets ?? ['heroicons'] as $set) {
                        // Check the selected style first, then fallback to similar styles
                        foreach ($stylesToCheck as $checkStyle) {
                            if (isset($searchResults[$set][$checkStyle])) {
                                $combinedIcons = array_merge($combinedIcons, $searchResults[$set][$checkStyle]);
                                break; // Found icons in this set, no need to check other styles
                            }
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
            // When "All" is selected, we need to intelligently map styles between icon sets
            $styleMapping = [
                'outline' => ['outline', 'regular'], // Heroicons outline ≈ Bootstrap regular
                'solid' => ['solid', 'fill'],         // Heroicons solid ≈ Bootstrap fill
                'mini' => ['mini', 'regular'],        // Heroicons mini ≈ Bootstrap regular
                'regular' => ['regular', 'outline'],  // Bootstrap regular ≈ Heroicons outline
                'fill' => ['fill', 'solid'],          // Bootstrap fill ≈ Heroicons solid
            ];

            foreach ($this->propertyStyles as $style) {
                $combinedIcons = [];
                $stylesToCheck = $styleMapping[$style] ?? [$style];

                foreach ($this->propertySets ?? ['heroicons'] as $set) {
                    // Check the selected style first, then fallback to similar styles
                    foreach ($stylesToCheck as $checkStyle) {
                        if (isset($allIconSets[$set][$checkStyle])) {
                            $combinedIcons = array_merge($combinedIcons, $allIconSets[$set][$checkStyle]);
                            break; // Found icons in this set, no need to check other styles
                        }
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

        $allIconSets = $this->getAllIconSets();

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
