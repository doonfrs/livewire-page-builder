<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImageProperty extends Component
{
    use WithFileUploads;

    public $propertyName;

    public $currentValue;

    public $propertyLabel;

    public $rowId;

    public $blockId;

    public $uploadedImage;

    public function render()
    {
        return view('page-builder::livewire.builder.block-properties.image-property');
    }

    public function uploadImage()
    {
        $path = $this->uploadedImage->store('page-builder', 'public');
        $url = Storage::url($path);
        $this->currentValue = $url;
        $this->dispatch('updateBlockProperty', $this->rowId, $this->blockId, $this->propertyName, $url);
    }

    public function removeImage()
    {
        $this->currentValue = null;
        $this->dispatch('updateBlockProperty', $this->rowId, $this->blockId, $this->propertyName, null);
    }
}
