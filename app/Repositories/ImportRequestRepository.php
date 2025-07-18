<?php

namespace App\Repositories;

use App\Constants\ImportRequests;
use App\Models\ImportRequest;
use Illuminate\Contracts\Pagination\Paginator;

class ImportRequestRepository
{

    public function storeImportRequest(string $filePath): ImportRequest
    {

        $importRequest = new ImportRequest();

        $importRequest->file_path = $filePath;
        $importRequest->status = ImportRequests::NEW;

        $importRequest->save();

        return $importRequest;

    }

    public function updateImportRequest(int $id, string $status = null, string $processedAt = null, string $errorReportPath = null): ImportRequest
    {
        $importRequest = ImportRequest::find($id);

        if (empty($importRequest)) {
            throw new \Exception("Import request with ID $id not found.");
        }

        if (empty($status) && empty($processedAt) && empty($errorReportPath)) {
            return $importRequest;
        }

        if (!empty($status)) {
            $importRequest->status = $status;
        }

        if (!empty($processedAt)) {
            $importRequest->processed_at = $processedAt;
        }

        if (!empty($errorReportPath)) {
            $importRequest->error_report_path = $errorReportPath;
        }

        $importRequest->save();

        return $importRequest;
    }

    public function findImportRequest(int $id): ImportRequest | null
    {
        return ImportRequest::find($id);
    }
    public function findImportRequests(string $status): Paginator
    {
        return ImportRequest::where('status', $status)->simplePaginate(ImportRequests::PAGINATION_PER_PAGE);
    }
}
