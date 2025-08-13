<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Support\Properties\BlockProperty;

class BlockProperties extends Component
{
    use WithFileUploads;

    public $rowId = null;

    public $blockId = null;

    public $properties = [];

    public $blockClass = null;

    public $blockLabel = null;

    public function render()
    {
        $blockProperties = [];
        $propertyGroups = [];

        if ($this->blockClass) {
            $blockProperties = $this->getCachedBlockProperties($this->blockClass);
            $propertyGroups = $this->makePropertyGroups($blockProperties);
        }

        return view('page-builder::livewire.builder.block-properties', [
            'blockProperties' => $blockProperties,
            'propertyGroups' => $propertyGroups,
        ]);
    }

    /**
     * Organize properties into groups for the UI
     */
    protected function makePropertyGroups(array $blockProperties): array
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

    public function updateBlockProperty($rowId, $blockId, $propertyName, $value)
    {
        $this->properties[$propertyName] = $value;
        $this->dispatch('updateBlockProperty', $rowId, $blockId, $propertyName, $value);
    }

    #[On('row-selected')]
    public function rowSelected($rowId, $properties = null)
    {
        if (is_array($rowId) && $properties === null) {
            $payload = $rowId;
            $rowId = $payload['rowId'] ?? null;
            $properties = $payload['properties'] ?? [];
        }
        $this->rowId = $rowId;
        $this->blockId = null;
        $this->properties = $properties;
        $this->blockClass = RowBlock::class;
        $this->blockLabel = Str::headline(class_basename(RowBlock::class));
        // Compute-heavy arrays are generated on render to keep payloads small
    }

    #[On('block-selected')]
    public function blockSelected($blockId = null, $properties = null, $blockClass = null)
    {
        if (is_array($blockId) && $properties === null && $blockClass === null) {
            $payload = $blockId;
            $blockId = $payload['blockId'] ?? null;
            $properties = $payload['properties'] ?? [];
            $blockClass = $payload['blockClass'] ?? null;
        }
        $this->blockId = $blockId;
        $this->rowId = null;
        $this->properties = $properties;

        if (isset($this->properties['blockPageName'])) {
            $this->blockClass = BuilderPageBlock::class;
        } else {
            $this->blockClass = $this->resolveBlockClass($blockClass);
        }
        $this->blockLabel = Str::headline(class_basename($this->blockClass));
        // Compute-heavy arrays are generated on render to keep payloads small
    }

    public function resolveBlockClass($md5Class)
    {
        $map = Cache::rememberForever('page-builder:block-md5-map', function () {
            $mapping = [];
            foreach (app(PageBuilderService::class)->getConfigBlocks() as $blockClass) {
                $mapping[md5($blockClass)] = $blockClass;
            }
            $mapping[md5(BuilderPageBlock::class)] = BuilderPageBlock::class;
            $mapping[md5(RowBlock::class)] = RowBlock::class;

            return $mapping;
        });

        return $map[$md5Class] ?? null;
    }

    protected function getCachedBlockProperties(string $blockClass): array
    {
        $cacheKey = 'page-builder:block-properties:'.md5($blockClass);

        return Cache::rememberForever($cacheKey, function () use ($blockClass) {
            return array_map(
                function (BlockProperty $property) {
                    return $property->toArray();
                },
                app($blockClass)->getAllProperties()
            );
        });
    }
}
