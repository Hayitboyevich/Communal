<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    public function uploadImage(UploadedFile $file, string $path): string
    {
        $filename = $this->generateUniqueFileName($file);
        return $file->storeAs($path, $filename, 'public');
    }


    public function uploadFile(UploadedFile $file, string $path): string
    {
        $filename = $this->generateUniqueFileName($file);
        return $file->storeAs($path, $filename, 'public');
    }


    private function generateUniqueFileName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        return $name . '_' . uniqid() . '.' . $extension;
    }


    public function deleteFile(string $path): bool
    {
        return Storage::disk('public')->delete($path);
    }

}
