<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StorageService
{
    /**
     * Store a file in the given disk/folder.
     */
    public function store(UploadedFile $file, string $folder, string $disk = 'public'): string
    {
        return $file->store($folder, $disk);
    }

    /**
     * Check if a file exists on the given disk.
     */
    public function exists(string $path, string $disk = 'public'): bool
    {
        if (empty($path)) {
            return false;
        }
        return Storage::disk($disk)->exists($path);
    }

    /**
     * Delete a file from the given disk.
     */
    public function delete(?string $path, string $disk = 'public'): bool
    {
        if (empty($path)) {
            return false;
        }

        if ($this->exists($path, $disk)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Download a file from the given disk.
     */
    public function download(string $path, ?string $name = null, string $disk = 'public'): StreamedResponse
    {
        return Storage::disk($disk)->download($path, $name);
    }
}
