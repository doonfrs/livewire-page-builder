<?php

namespace Trinavo\LivewirePageBuilder\Services;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Trinavo\LivewirePageBuilder\Contracts\StoresUploadedImages;

/**
 * Stores the upload as it arrived. The package's standalone behaviour, bound
 * only when the host application has not bound its own.
 */
class DefaultUploadedImageStore implements StoresUploadedImages
{
    public function store(TemporaryUploadedFile $file, string $directory, string $disk): ?string
    {
        return $file->store($directory, $disk) ?: null;
    }
}
