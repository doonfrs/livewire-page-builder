<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Trinavo\LivewirePageBuilder\Support\Concerns\DispatchesBlockPropertyUpdate;

class VideoProperty extends Component
{
    use DispatchesBlockPropertyUpdate;
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
        Log::debug('VideoProperty::updatedUploadedVideo hook triggered');
        $this->uploadVideo();
    }

    public function uploadVideo()
    {
        Log::debug('VideoProperty::uploadVideo called', [
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

        Log::debug('VideoProperty::uploadVideo - Starting validation');

        $this->validate([
            'uploadedVideo' => 'required|file|mimetypes:video/mp4,video/webm,video/ogg|max:51200', // max 50MB
        ]);

        Log::debug('VideoProperty::uploadVideo - Validation passed, storing file');

        $path = $this->uploadedVideo->store(path: 'page-builder', options: 'public');
        // Ask the disk rather than concatenating the configured base URL by hand: the
        // tenant prefix lives in the base URL under the local driver and in the disk
        // root under S3, and only the adapter knows how to combine the two.
        $url = Storage::disk('public')->url(ltrim($path, '/'));

        Log::debug('VideoProperty::uploadVideo - File stored', [
            'path' => $path,
            'url' => $url,
        ]);

        $this->currentValue = $url;
        $this->dispatchBlockPropertyUpdate($this->propertyName, $url);

        Log::debug('VideoProperty::uploadVideo - Dispatched updateBlockProperty event', [
            'url' => $url,
        ]);

        // Reset uploadedVideo after processing
        $this->reset('uploadedVideo');
    }

    public function updateVideoUrl()
    {
        $this->dispatchBlockPropertyUpdate($this->propertyName, $this->currentValue);
    }

    public function removeVideo()
    {
        $this->currentValue = null;
        $this->dispatchBlockPropertyUpdate($this->propertyName, null);
    }
}
