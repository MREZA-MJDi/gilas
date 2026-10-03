<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    public function store(UploadedFile $file, string $directory = 'uploads'): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $filename = Str::uuid() . '.' . $extension;

        return $file->storeAs(trim($directory, '/'), $filename, 'public');
    }

    public function replace(?string $oldPath, UploadedFile $file, string $directory = 'uploads'): string
    {
        $newPath = $this->store($file, $directory);

        if ($oldPath) {
            $this->delete($oldPath);
        }

        return $newPath;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    public function url(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
