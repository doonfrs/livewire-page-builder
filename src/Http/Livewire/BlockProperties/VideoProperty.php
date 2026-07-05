<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

use Illuminate\Support\Facades\Log;
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
        // Build the URL from the tenant-aware public disk URL. Do NOT use the disk's
        // url() helper: the S3 driver re-applies the tenant root prefix, double-prefixing
        // the key under S3. This concat matches local behavior byte-for-byte.
        $url = rtrim((string) config('filesystems.disks.public.url'), '/').'/'.ltrim($path, '/');

        Log::debug('VideoProperty::uploadVideo - File stored', [
            'path' => $path,
            'url' => $url,
        ]);

        $this->currentValue = $url;
        $this->dispatch(event: 'updateBlockProperty', rowId: $this->rowId, blockId: $this->blockId, propertyName: $this->propertyName, value: $url);

        Log::debug('VideoProperty::uploadVideo - Dispatched updateBlockProperty event', [
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
