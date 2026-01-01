<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FileUploadService
{
    public static function uploadProof(UploadedFile $file): string
    {
        $allowed = ['image/jpeg', 'image/png', 'application/pdf'];

        if (!in_array($file->getMimeType(), $allowed)) {
            throw new \Exception('Invalid file type');
        }

        if ($file->getSize() > config('app.upload_max_size') * 1024) {
            throw new \Exception('File too large');
        }

        $filename = Str::uuid() . '.' . $file->extension();
        $path = $file->storeAs('private/proofs', $filename);

        return $path;
    }
}
