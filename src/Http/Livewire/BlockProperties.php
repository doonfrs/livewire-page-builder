<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Trinavo\LivewirePageBuilder\Support\Concerns\OrganizesBlockProperties;
use Trinavo\LivewirePageBuilder\Support\Properties\BlockProperty;

class BlockProperties extends Component
{
    use OrganizesBlockProperties;
    use WithFileUploads;

    public $rowId = null;

    public $blockId = null;

    public $properties = [];

    public $blockProperties = [];

    public $propertyGroups = [];

    public $blockClass = null;

    public $blockLabel = null;

    public bool $componentMissing = false;

    public ?string $missingBlockAlias = null;

    public function render()
    {
        if (! empty($this->blockProperties)) {
            $this->propertyGroups = $this->organizeProperties($this->blockProperties);
        }

        return view('page-builder::livewire.builder.block-properties', [
            'blockProperties' => $this->blockProperties,
            'propertyGroups' => $this->propertyGroups,
        ]);
    }

    public function updateBlockProperty($rowId, $blockId, $propertyName, $value)
    {
        $this->properties[$propertyName] = $value;
        $this->dispatch('updateBlockProperty', $rowId, $blockId, $propertyName, $value);
    }

    #[On('row-selected')]
    public function rowSelected($rowId, $properties)
    {
        $this->componentMissing = false;
        $this->missingBlockAlias = null;

        $this->rowId = $rowId;
        $this->blockId = null;
        $this->properties = $properties;
        $this->blockClass = RowBlock::class;
        $this->blockLabel = Str::headline(class_basename(RowBlock::class));
        $this->blockProperties =
            array_map(function (BlockProperty $property) {
                return $property->toArray();
            }, app(RowBlock::class)->getAllProperties());

        $this->propertyGroups = $this->organizeProperties($this->blockProperties);
    }

    #[On('block-selected')]
    public function blockSelected($blockId, $properties, $blockClass, $blockAlias = null)
    {
        $this->componentMissing = false;
        $this->missingBlockAlias = null;

        $this->blockId = $blockId;
        $this->rowId = null;
        $this->properties = $properties;

        if (isset($this->properties['blockPageName'])) {
            $this->blockClass = BuilderPageBlock::class;
        } else {
            $this->blockClass = $this->resolveBlockClass($blockClass);
        }

        if (! $this->blockClass || ! class_exists($this->blockClass)) {
            $this->componentMissing = true;
            $this->missingBlockAlias = $blockAlias;
            $this->blockLabel = __('Missing block component');
            $this->blockProperties = [];
            $this->propertyGroups = [];

            return;
        }

        $this->blockLabel = Str::headline(class_basename($this->blockClass));

        $blockInstance = app($this->blockClass);

        $this->blockProperties =
            array_map(function (BlockProperty $property) {
                return $property->toArray();
            }, $blockInstance->getAllProperties());

        // Merge defaults for any missing properties so editors show default values
        $defaults = $blockInstance->getPropertyValues();
        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $this->properties)) {
                $this->properties[$key] = $value;
            }
        }

        $this->propertyGroups = $this->organizeProperties($this->blockProperties);
    }

    public function resolveBlockClass($md5Class)
    {
        foreach (app(PageBuilderService::class)->getConfigBlocks() as $blockClass) {
            if (md5($blockClass) === $md5Class) {
                return $blockClass;
            }
        }

        return null;
    }
}
