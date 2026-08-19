<?php

namespace Trinavo\LivewirePageBuilder\Support\Concerns;

/**
 * Turns a flat list of BlockProperty::toArray() schemas into the grouped structure the
 * property views render. Shared by the builder's panel and the live edit sheet.
 */
trait OrganizesBlockProperties
{
    /**
     * Group properties for the UI.
     *
     * Ungrouped properties collect into a leading "general" group so a block's own
     * settings always appear first.
     *
     * @param  array  $blockProperties  BlockProperty::toArray() entries
     * @return array group name => ['label', 'columns', 'icon', 'properties']
     */
    protected function organizeProperties(array $blockProperties): array
    {
        $groups = [];
        $defaultProperties = [];

        foreach ($blockProperties as $property) {
            if (! empty($property['group'])) {
                $groupName = $property['group'];
                if (! isset($groups[$groupName])) {
                    $groups[$groupName] = [
                        'label' => $property['groupLabel'] ?? ucfirst($groupName),
                        'columns' => $property['groupColumns'] ?? 1,
                        'icon' => $property['groupIcon'] ?? $this->getDefaultGroupIcon($groupName),
                        'properties' => [],
                    ];
                }
                $groups[$groupName]['properties'][] = $property;
            } else {
                $defaultProperties[] = $property;
            }
        }

        if (! empty($defaultProperties)) {
            $groups['general'] = [
                'label' => __('Block Settings'),
                'columns' => 1,
                'icon' => 'heroicon-o-cog-6-tooth',
                'properties' => $defaultProperties,
            ];

            // Move general group to the beginning
            $groups = array_merge(
                ['general' => $groups['general']],
                array_diff_key($groups, ['general' => null])
            );
        }

        return $groups;
    }

    /**
     * Get default icon for a group based on its name
     */
    protected function getDefaultGroupIcon(string $groupName): string
    {
        return match ($groupName) {
            'responsive' => 'heroicon-o-device-phone-mobile',
            'visibility' => 'heroicon-o-eye',
            'appearance' => 'heroicon-o-swatch',
            'content' => 'heroicon-o-document-text',
            'layout' => 'heroicon-o-rectangle-group',
            'animation' => 'heroicon-o-arrow-path',
            default => 'heroicon-o-tag',
        };
    }
}
