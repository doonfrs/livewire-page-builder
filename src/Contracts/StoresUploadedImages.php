<?php

namespace Trinavo\LivewirePageBuilder\Contracts;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * How an uploaded image is moved from Livewire's temporary disk to permanent
 * storage.
 *
 * The package ships DefaultUploadedImageStore, which simply stores the file.
 * A host application that needs more - resizing, a dimension cap, a CDN, its
 * own naming - binds its own implementation in a service provider, and every
 * builder upload follows the same rules as the rest of that application.
 */
interface StoresUploadedImages
{
    /**
     * @return string|null the stored path relative to the disk, or null when the
     *                     file was rejected
     */
    public function store(TemporaryUploadedFile $file, string $directory, string $disk): ?string;
}
