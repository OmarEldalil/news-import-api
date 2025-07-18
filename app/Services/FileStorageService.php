<?php

namespace App\Services;

use App\Exceptions\StoreCSVFileException;
use Illuminate\Support\Facades\Storage;

class FileStorageService
{

    static function getFileStoragePath(string $directory, string $fileName): string
    {
        return $directory . '/' . $fileName;
    }
    static function getFullQualifiedFileStoragePath(string $filePath): string
    {
        return Storage::disk('local')->path($filePath);
    }
    public function storePublicFile(string $directory, string $fileName, $fileContent): string {
        return $this->storeFile($directory, $fileName, 'public', $fileContent);
    }

    public function storePrivateFile(string $directory, string $fileName, $fileContent): string {
        return $this->storeFile($directory, $fileName, 'local', $fileContent);
    }

    private function storeFile(string $directory, string $fileName, string $disk, $fileContent): string
    {
        $storagePath = self::getFileStoragePath($directory, $fileName);
        $path = Storage::disk($disk)->put($storagePath, $fileContent);
        if ($path === false) {
            throw new StoreCSVFileException("Failed to store file at path: $storagePath");
        }
        return $storagePath;
    }

}
