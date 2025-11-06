<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class VideoProperty extends Component
{
    use WithFileUploads;

    public $propertyName;

    public $currentValue;

    public $propertyLabel;

    public $rowId;

    public $blockId;

    public $uploadedVideo;

    public function render()
    {
        return view('page-builder::livewire.builder.block-properties.video-property');
    }

    /**
     * Automatically called when uploadedVideo property is updated
     */
    public function updatedUploadedVideo()
    {
        Log::info('VideoProperty::updatedUploadedVideo hook triggered');
        $this->uploadVideo();
    }

    public function uploadVideo()
    {
        Log::info('VideoProperty::uploadVideo called', [
            'propertyName' => $this->propertyName,
            'rowId' => $this->rowId,
            'blockId' => $this->blockId,
            'uploadedVideo' => $this->uploadedVideo ? 'Present' : 'NULL',
        ]);

        // Validate that uploadedVideo exists before processing
        if (! $this->uploadedVideo) {
            Log::warning('VideoProperty::uploadVideo - uploadedVideo is null, returning early');

            return;
        }

        Log::info('VideoProperty::uploadVideo - Starting validation');

        $this->validate([
            'uploadedVideo' => 'required|file|mimetypes:video/mp4,video/webm,video/ogg|max:51200', // max 50MB
        ]);

        Log::info('VideoProperty::uploadVideo - Validation passed, storing file');

        $path = $this->uploadedVideo->store(path: 'page-builder', options: 'public');
        $url = Storage::url(path: $path);

        Log::info('VideoProperty::uploadVideo - File stored', [
            'path' => $path,
            'url' => $url,
        ]);

        $this->currentValue = $url;
        $this->dispatch(event: 'updateBlockProperty', rowId: $this->rowId, blockId: $this->blockId, propertyName: $this->propertyName, value: $url);

        Log::info('VideoProperty::uploadVideo - Dispatched updateBlockProperty event', [
            'url' => $url,
        ]);

        // Reset uploadedVideo after processing
        $this->reset('uploadedVideo');
    }

    public function updateVideoUrl()
    {
        $this->dispatch(event: 'updateBlockProperty', rowId: $this->rowId, blockId: $this->blockId, propertyName: $this->propertyName, value: $this->currentValue);
    }

    public function removeVideo()
    {
        $this->currentValue = null;
        $this->dispatch(event: 'updateBlockProperty', rowId: $this->rowId, blockId: $this->blockId, propertyName: $this->propertyName, value: null);
    }
}
