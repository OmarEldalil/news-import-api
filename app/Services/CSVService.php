<?php

namespace App\Services;

use App\Constants\ImportRequests;
use InvalidArgumentException;
use Illuminate\Http\UploadedFile;
use function PHPUnit\Framework\isArray;

class CSVService
{
    private FileStorageService $fileStorageService;

    public function __construct(FileStorageService $fileStorageService)
    {
        $this->fileStorageService = $fileStorageService;
    }


    private function readCSVHeader(string $filePath): array
    {
        $handle = $this->getFileHandle($filePath);

        try {
            $header = fgetcsv($handle, '4096');

            if ($header === false) {
                throw new InvalidArgumentException('Unable to read CSV headers');
            }

        } finally {
            fclose($handle);
        }

        return $header;
    }

    public function validateCsvHeader(UploadedFile $file, array $expectedHeader): bool
    {
        if (!$file->isValid()) {
            throw new InvalidArgumentException('Invalid file uploaded');
        }

        $header = $this->readCSVHeader($file->getPathname());

        $header = $this->sanitizeCsvHeader($header);
        $expectedHeader = $this->sanitizeCsvHeader($expectedHeader);

        $differences = array_diff($expectedHeader, $header);
        if (!empty($differences)) {
            throw new InvalidArgumentException('CSV headers do not match expected format -> "' . implode(', ', $differences) . '" not found');
        }
        return true;

    }

    private function sanitizeCsvHeader(array $header): array
    {
        return array_map(fn($h) => trim(strtolower($h)), $header);
    }

    public function storeCSV(string $directory, string $fileName, string $fileContent, $isPrivate = true): string
    {
        if($isPrivate){
            return $this->fileStorageService->storePrivateFile($directory, $fileName . '.csv', $fileContent);
        }
        return $this->fileStorageService->storePublicFile($directory, $fileName . '.csv', $fileContent);
    }

    public function getCSVRows(string $filePath, callable $cb, int $chunkSize = ImportRequests::CHUNK_SIZE): array
    {
        $handle = $this->getFileHandle($filePath);

        try {
            $rowNumber = 0;
            $currentChunk = [];
            $header = fgetcsv($handle, '4096');

            if ($header === false) {
                throw new InvalidArgumentException('Unable to read CSV headers');
            }

            while ($row = fgetcsv($handle, '4096')) {
                $rowNumber++;
                $currentChunk[$rowNumber] = array_combine($header, $row);
                if ($rowNumber % $chunkSize === 0) {
                    \Log::info('chunk', ['rowNumber' => $rowNumber, 'currentChunkSize' => count($currentChunk)]);
                    // Call the callback with the current chunk of rows
                    $cb($currentChunk);
                    $currentChunk = [];
                }
            }
            \Log::info('last chunk', ['rowNumber' => $rowNumber, 'currentChunkSize' => count($currentChunk)]);
            // If there are any remaining rows in the current chunk, call the callback
            if (!empty($currentChunk)) {
                $cb($currentChunk);
            }

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        } finally {
            fclose($handle);
        }

        return $header;

    }

    private function getFileHandle(string $filePath)
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new InvalidArgumentException('Unable to open CSV file');
        }
        return $handle;
    }

    public function createCsv(array $rows): string
    {
        if (empty($rows)) {
            return '';
        }
        $header = array_keys($rows[0]);
        $csvContent = implode(',', $header) . "\n";

        foreach ($rows as $row) {
            $sanitizedRow = array_map(function ($value) {
                $escapedValue = str_replace('"', '""', $value);
                if(str_contains($escapedValue, "\n") || str_contains($escapedValue, "\r")) {
                    return '"' . $escapedValue . '"';
                }
                return $escapedValue;
            }, array_values($row));

            \Log::info('CSV Row', ['row' => $sanitizedRow]);
            $csvContent .= implode(',', $sanitizedRow) . "\n";
        }
        return $csvContent;
    }
}

