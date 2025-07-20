<?php

namespace App\Services;

use App\Constants\Errors;
use App\Exceptions\StorageException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileStorageService
{

    static function getFileStoragePath(string $directory, string $fileName): string
    {
        return $directory . '/' . $fileName;
    }

    static function getFullQualifiedFileStoragePath(string $filePath, bool $isPrivate = true): string
    {
        return Storage::disk($isPrivate ? 'local' : 'public')->path($filePath);
    }

    /**
     * @throws StorageException
     */
    public function storePublicFile(string $directory, string $fileName, UploadedFile|string $fileContent): string
    {
        return $this->storeFile($directory, $fileName, 'public', $fileContent);
    }

    /**
     * @throws StorageException
     */
    public function storePrivateFile(string $directory, string $fileName, UploadedFile|string $fileContent): string
    {
        return $this->storeFile($directory, $fileName, 'local', $fileContent);
    }

    /**
     * @throws StorageException
     */
    private function storeFile(string $directory, string $fileName, string $disk, UploadedFile|string $fileContent): string
    {
        $storagePath = self::getFileStoragePath($directory, $fileName);
        if (is_string($fileContent)) {
            $path = Storage::disk($disk)->put($storagePath, $fileContent);
        } else {
            $path = Storage::disk($disk)->putFileAs($directory, $fileContent, $fileName);
        }
        if ($path === false) {
            throw new StorageException(Errors::STORAGE_ERROR, "Failed to store file at path: $storagePath");
        }
        return $storagePath;
    }
}
