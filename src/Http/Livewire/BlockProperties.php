<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Illuminate\Support\Facades\Storage;
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

    public $blockProperties = [];

    public $propertyGroups = [];

    public $blockClass = null;

    public $blockLabel = null;

    public $uploadedImage;

    public function render()
    {
        if (! empty($this->blockProperties)) {
            $this->organizeProperties();
        }

        return view('page-builder::builder.block-properties', [
            'blockProperties' => $this->blockProperties,
            'propertyGroups' => $this->propertyGroups,
        ]);
    }

    /**
     * Organize properties into groups for the UI
     */
    protected function organizeProperties()
    {
        $this->propertyGroups = [];
        $defaultProperties = [];

        // First pass - separate default properties and process group properties
        foreach ($this->blockProperties as $property) {
            if (! empty($property['group'])) {
                $groupName = $property['group'];
                if (! isset($this->propertyGroups[$groupName])) {
                    $this->propertyGroups[$groupName] = [
                        'label' => $property['groupLabel'] ?? ucfirst($groupName),
                        'columns' => $property['groupColumns'] ?? 1,
                        'icon' => $property['groupIcon'] ?? $this->getDefaultGroupIcon($groupName),
                        'properties' => [],
                    ];
                }
                $this->propertyGroups[$groupName]['properties'][] = $property;
            } else {
                $defaultProperties[] = $property;
            }
        }

        // Add default properties as "general" group if any exist
        if (! empty($defaultProperties)) {
            $this->propertyGroups['general'] = [
                'label' => 'Block Settings',
                'columns' => 1,
                'icon' => 'heroicon-o-cog-6-tooth',
                'properties' => $defaultProperties,
            ];

            // Move general group to the beginning
            $this->propertyGroups = array_merge(
                ['general' => $this->propertyGroups['general']],
                array_diff_key($this->propertyGroups, ['general' => null])
            );
        }
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
        $this->skipRender();
    }

    public function uploadImage($rowId, $blockId, $propertyName)
    {
        if (! $this->uploadedImage) {
            return;
        }

        $path = $this->uploadedImage->store('page-builder', 'public');
        $url = Storage::url($path);
        $this->properties[$propertyName] = $url;
        $this->uploadedImage = null;

        $this->dispatch('updateBlockProperty', $rowId, $blockId, $propertyName, $url);
    }

    #[On('row-selected')]
    public function rowSelected($rowId, $properties)
    {
        $this->rowId = $rowId;
        $this->blockId = null;
        $this->properties = $properties;
        $this->blockClass = RowBlock::class;
        $this->blockLabel = Str::headline(class_basename(RowBlock::class));
        $this->blockProperties =
            array_map(function (BlockProperty $property) {
                return $property->toArray();
            }, app(RowBlock::class)->getAllProperties());

        $this->organizeProperties();
    }

    #[On('block-selected')]
    public function blockSelected($blockId, $properties, $blockClass)
    {
        $this->blockId = $blockId;
        $this->rowId = null;
        $this->properties = $properties;

        if (isset($this->properties['blockPageName'])) {
            $this->blockClass = BuilderPageBlock::class;
        } else {
            $this->blockClass = $this->resolveBlockClass($blockClass);
        }
        $this->blockLabel = Str::headline(class_basename($this->blockClass));
        $this->blockProperties =
            array_map(function (BlockProperty $property) {
                return $property->toArray();
            }, app($this->blockClass)->getAllProperties());

        $this->organizeProperties();
    }

    public function resolveBlockClass($md5Class)
    {
        foreach (app(PageBuilderService::class)->getConfigBlocks() as $blockClass) {
            if (md5($blockClass) === $md5Class) {
                return $blockClass;
            }
        }
    }
}
