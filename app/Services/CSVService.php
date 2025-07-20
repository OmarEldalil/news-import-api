<?php

namespace App\Services;

use App\Constants\Errors;
use App\Constants\ImportRequests;
use App\Exceptions\CSVFileException;
use App\Exceptions\StorageException;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class CSVService
{
    private FileStorageService $fileStorageService;

    public function __construct(FileStorageService $fileStorageService)
    {
        $this->fileStorageService = $fileStorageService;
    }


    /**
     * @throws CSVFileException
     */
    private function readCSVHeader(string $filePath): array
    {
        $handle = $this->getFileOpenHandle($filePath);

        try {
            $header = fgetcsv($handle, '4096');

            if ($header === false) {
                throw new CSVFileException(Errors::UNABLE_TO_READ_CSV_HEADER_ERROR);
            }

        } finally {
            fclose($handle);
        }

        return $header;
    }

    /**
     * @throws CSVFileException
     */
    public function validateCsvFileHeader(UploadedFile $file, array $expectedHeader): bool
    {
        if (!$file->isValid()) {
            throw new CSVFileException(Errors::INVALID_FILE_ERROR);
        }

        $header = $this->readCSVHeader($file->getPathname());

        $header = $this->sanitizeCsvHeader($header);
        $expectedHeader = $this->sanitizeCsvHeader($expectedHeader);

        $differences = array_diff($expectedHeader, $header);
        if (!empty($differences)) {
            throw new CSVFileException(Errors::CSV_HEADER_DOES_NOT_MATCH_ERROR, 'The CSV header does not match the expected format. Missing columns: ' . implode(', ', $differences));
        }
        return true;

    }

    private function sanitizeCsvHeader(array $header): array
    {
        return array_map(fn($h) => trim(strtolower($h)), $header);
    }

    /**
     * @throws StorageException
     */
    public function storeCSV(string $directory, string $fileName, UploadedFile|string $file, $isPrivate = true): string
    {
        if ($isPrivate) {
            return $this->fileStorageService->storePrivateFile($directory, $fileName . '.csv', $file);
        }
        return $this->fileStorageService->storePublicFile($directory, $fileName . '.csv', $file);
    }

    /**
     * Processes CSV rows in chunks and calls the provided callback for each chunk in order not to load the entire file into memory.
     * This is an efficient way to handle large CSV files.
     * @throws CSVFileException
     */
    public function processCSVRowsStream(string $filePath, callable $cb, int $chunkSize = ImportRequests::CHUNK_SIZE): void
    {
        $handle = $this->getFileOpenHandle($filePath);

        $rowNumber = 0;
        try {
            $currentChunk = [];
            $currentParseErrors = [];
            $header = fgetcsv($handle, '4096');

            if ($header === false) {
                throw new CSVFileException(Errors::UNABLE_TO_READ_CSV_HEADER_ERROR);
            }

            while ($row = fgetcsv($handle, '4096')) {
                $rowNumber++;

                if (count($row) !== count($header)) {
                    $currentParseErrors[] = ['column_number' => $rowNumber, 'error' => 'CSV row does not match header length'];
                } else {
                    $currentChunk[$rowNumber] = array_combine($header, $row);
                }

                if ($rowNumber % $chunkSize === 0) {
                    $cb($currentChunk, $currentParseErrors);
                    $currentChunk = [];
                    $currentParseErrors = [];
                }
            }
            Log::info('last chunk', ['rowNumber' => $rowNumber, 'currentChunkSize' => count($currentChunk)]);
            // If there are any remaining rows in the current chunk, call the callback
            if (!empty($currentChunk)) {
                $cb($currentChunk, $currentParseErrors);
            }

        } catch (CSVFileException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Error processing CSV file: ' . $e->getMessage(), [
                'file_path' => $filePath,
                'row_number' => $rowNumber,
            ]);
        } finally {
            fclose($handle);
        }
    }

    /**
     * @throws CSVFileException
     */
    private function getFileOpenHandle(string $filePath)
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new CSVFileException(Errors::UNABLE_TO_OPEN_CSV_ERROR);
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
                if (str_contains($escapedValue, "\n") || str_contains($escapedValue, "\r")) {
                    return '"' . $escapedValue . '"';
                }
                return $escapedValue;
            }, array_values($row));

            $csvContent .= implode(',', $sanitizedRow) . "\n";
        }
        return $csvContent;
    }
}

