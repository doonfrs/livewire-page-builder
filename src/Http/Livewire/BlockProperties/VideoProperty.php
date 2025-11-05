<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire\BlockProperties;

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

    public function uploadVideo()
    {
        $this->validate([
            'uploadedVideo' => 'required|file|mimetypes:video/mp4|max:5120', // 5MB = 5120KB
        ]);

        $path = $this->uploadedVideo->store(path: 'page-builder', options: 'public');
        $url = Storage::url(path: $path);
        $this->currentValue = $url;
        $this->dispatch(event: 'updateBlockProperty', rowId: $this->rowId, blockId: $this->blockId, propertyName: $this->propertyName, value: $url);
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
