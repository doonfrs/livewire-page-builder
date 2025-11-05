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

    /**
     * Automatically called when uploadedImage property is updated
     */
    public function updatedUploadedImage()
    {
        \Log::info('ImageProperty::updatedUploadedImage hook triggered');
        $this->uploadImage();
    }

    public function uploadImage()
    {
        \Log::info('ImageProperty::uploadImage called', [
            'propertyName' => $this->propertyName,
            'rowId' => $this->rowId,
            'blockId' => $this->blockId,
            'uploadedImage' => $this->uploadedImage ? 'Present' : 'NULL',
        ]);

        // Validate that uploadedImage exists before processing
        if (! $this->uploadedImage) {
            \Log::warning('ImageProperty::uploadImage - uploadedImage is null, returning early');
            return;
        }

        \Log::info('ImageProperty::uploadImage - Starting validation');

        try {
            $this->validate([
                'uploadedImage' => 'image|max:10240', // max 10MB
            ]);

            \Log::info('ImageProperty::uploadImage - Validation passed, storing file');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('ImageProperty::uploadImage - Validation failed', [
                'errors' => $e->errors(),
                'message' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('ImageProperty::uploadImage - Unexpected error during validation', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        $path = $this->uploadedImage->store('page-builder', 'public');
        $url = Storage::url($path);

        \Log::info('ImageProperty::uploadImage - File stored', [
            'path' => $path,
            'url' => $url,
        ]);

        $this->currentValue = $url;
        $this->dispatch('updateBlockProperty', $this->rowId, $this->blockId, $this->propertyName, $url);

        \Log::info('ImageProperty::uploadImage - Dispatched updateBlockProperty event', [
            'url' => $url,
        ]);

        // Reset uploadedImage after processing
        $this->reset('uploadedImage');
    }

    public function updateImageUrl()
    {
        $this->dispatch('updateBlockProperty', $this->rowId, $this->blockId, $this->propertyName, $this->currentValue);
    }

    public function removeImage()
    {
        $this->currentValue = null;
        $this->dispatch('updateBlockProperty', $this->rowId, $this->blockId, $this->propertyName, null);
    }
}
