<?php

namespace App\Services;

use App\Constants\ImportRequests;
use App\Events\ImportRequestCreated;
use App\Exceptions\NotFoundException;
use App\Models\ImportRequest;
use App\Repositories\ImportRequestRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImportRequestService
{
    protected CSVService $csvService;
    private ImportRequestRepository $importRequestRepository;
    private NewsService $newsService;

    public function __construct(CSVService $csvService, ImportRequestRepository $importRequestRepository, NewsService $newsService)
    {
        $this->csvService = $csvService;
        $this->importRequestRepository = $importRequestRepository;
        $this->newsService = $newsService;
    }

    public function storeImportRequest(UploadedFile $csvFile): ImportRequest
    {
        $this->csvService->validateCsvHeader($csvFile, ImportRequests::REQUIRED_CSV_HEADERS);

        $fileName = Str::uuid()->toString();

        $storagePath = $this->csvService->storeCSV(ImportRequests::IMPORT_REQUESTS_CSV_FILES_DIRECTORY, $fileName, $csvFile->getContent());

        $importRequest = $this->importRequestRepository->storeImportRequest($storagePath);

        ImportRequestCreated::dispatch($importRequest);

        return $importRequest;

    }

    public function processImportRequest(ImportRequest $importRequest): void
    {
        $this->importRequestRepository->updateImportRequest($importRequest->id, ImportRequests::IN_PROGRESS);
        $errors = [];
        $this->csvService->getCSVRows(
            ($importRequest->file_path),
            function ($rows) use ($importRequest, &$errors) {
                $batchErrors = $this->newsService->storeNewsChunk($rows);
                $errors = array_merge($errors, $batchErrors);
            });

        $this->handleImportCompletion($importRequest, $errors);

    }

    private function handleImportCompletion(ImportRequest $importRequest, array $errors)
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

    public function getImportRequests(?int $id, ?string $status): ImportRequest | Paginator | null
    {
        if ($id) {
            $importRequest = $this->importRequestRepository->findImportRequest($id);
            if (empty($importRequest)) {
                throw new NotFoundException("Import request with ID $id not found.");
            }
            return $importRequest;
        }

        return $this->importRequestRepository->findImportRequests($status);
    }

}
