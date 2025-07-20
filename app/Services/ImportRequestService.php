<?php

namespace App\Services;

use App\Constants\Errors;
use App\Constants\ImportRequests;
use App\Events\ImportRequestCreated;
use App\Exceptions\CSVFileException;
use App\Exceptions\ImportRequestException;
use App\Exceptions\StorageException;
use App\Models\ImportRequest;
use App\Repositories\ImportRequestRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ImportRequestService
{
    private CSVService $csvService;
    private ImportRequestRepository $importRequestRepository;
    private NewsService $newsService;

    public function __construct(CSVService $csvService, ImportRequestRepository $importRequestRepository, NewsService $newsService)
    {
        $this->csvService = $csvService;
        $this->importRequestRepository = $importRequestRepository;
        $this->newsService = $newsService;
    }

    /**
     * @throws CSVFileException|StorageException
     */
    public function storeImportRequest(UploadedFile $csvFile): ImportRequest
    {
        $this->csvService->validateCsvFileHeader($csvFile, ImportRequests::REQUIRED_CSV_HEADERS);

        $storagePath = $this->csvService->storeCSV(ImportRequests::IMPORT_REQUESTS_CSV_FILES_DIRECTORY, Str::uuid()->toString(), $csvFile);

        $importRequest = $this->importRequestRepository->storeImportRequest(
            $storagePath,
            substr(htmlentities($csvFile->getClientOriginalName(), ENT_QUOTES, 'UTF-8'), 0, ImportRequests::MAX_FILE_NAME_LENGTH)
        );

        ImportRequestCreated::dispatch($importRequest);

        return $importRequest;

    }

    /**
     * @throws CSVFileException|StorageException
     */
    public function processImportRequest(ImportRequest $importRequest): void
    {
        $this->handleImportRequestStart($importRequest);

        $errors = $this->executeImportRequestBatches($importRequest);

        $this->handleImportCompletion($importRequest, $errors);

    }

    private function handleImportRequestStart(ImportRequest $importRequest): void
    {
        $this->importRequestRepository->updateImportRequest($importRequest->id, ImportRequests::IN_PROGRESS);
    }

    /**
     * @throws CSVFileException
     */
    private function executeImportRequestBatches(ImportRequest $importRequest): array
    {
        $errors = [];
        $this->csvService->processCSVRowsStream(
            $this->getImportCsvFileStoragePath($importRequest->file_path),
            function ($rows, $parseErrors) use ($importRequest, &$errors) {
                $batchErrors = $this->newsService->storeNewsChunk($rows, $parseErrors);
                $errors = array_merge($errors, $batchErrors);
            });
        return $errors;
    }

    /**
     * @throws StorageException
     */
    private function handleImportCompletion(ImportRequest $importRequest, array $errors): void
    {
        if (count($errors)) {
            $csvFileName = (string)Str::uuid();
            $csvContent = $this->csvService->createCsv($errors);
            $storagePath = $this->csvService->storeCSV(ImportRequests::ERRORS_CSV_FILES_DIRECTORY, $csvFileName, $csvContent, false);
        }

        $this->importRequestRepository->updateImportRequest(
            $importRequest->id,
            count($errors) ? ImportRequests::ERROR : ImportRequests::COMPLETED,
            Carbon::now()->format('Y-m-d H:i:s'),
            $storagePath ?? null
        );
    }

    /**
     * @throws ImportRequestException
     */
    public function getImportRequests(?int $id, ?string $status): Collection | ImportRequest
    {
        if ($id) {
            $importRequest = $this->importRequestRepository->findImportRequest($id);
            if (empty($importRequest)) {
                throw new ImportRequestException(Errors::NOT_FOUND_ERROR, "Import request not found.");
            }
            return $importRequest;
        }

        $paginatedImportRequests = $this->importRequestRepository->getImportRequestsWithStatus($status);

        return $this->transformImportRequests($paginatedImportRequests);
    }

    private function transformImportRequests(CursorPaginator $importRequests): Collection
    {
        return collect([
            'records' => $importRequests->items(),
            'cursor' => $importRequests->nextCursor()?->encode(),
        ]);
    }

    private function getImportCsvFileStoragePath(string $fileName): string
    {
        return FileStorageService::getFullQualifiedFileStoragePath($fileName);
    }
}
