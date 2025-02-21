<?php

namespace App\Services;

class FileService
{
    public function uploadImage($file, $path)
    {
        return $file->store($path, 'public');
    }
}
