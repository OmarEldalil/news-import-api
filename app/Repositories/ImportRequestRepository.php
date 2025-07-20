<?php

namespace App\Repositories;

use App\Constants\ImportRequests;
use App\Models\ImportRequest;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Log;

class ImportRequestRepository
{

    public function storeImportRequest(string $filePath, $originalFileName): ImportRequest
    {

        $importRequest = new ImportRequest();

        $importRequest->file_path = $filePath;
        $importRequest->original_file_name = $originalFileName;
        $importRequest->status = ImportRequests::NEW;

        $importRequest->save();

        return $importRequest;

    }

    public function updateImportRequest(int $id, ?string $status = null, ?string $processedAt = null, ?string $errorReportPath = null): ImportRequest|null
    {
        $importRequest = ImportRequest::find($id);

        if (empty($importRequest)) {
            Log::warning('ImportRequest not found for ID when update record: ' . $id);
            return null;
        }

        $updates = array_filter([
            'status' => $status,
            'processed_at' => $processedAt,
            'error_report_path' => $errorReportPath,
        ]);

        if ($updates) {
            $importRequest->update($updates);
        }

        return $importRequest;
    }

    public function findImportRequest(int $id): ImportRequest|null
    {
        return ImportRequest::find($id);
    }

    public function getImportRequestsWithStatus(string $status): CursorPaginator
    {
        return ImportRequest::orderBy('id', 'DESC')->where('status', $status)->cursorPaginate(ImportRequests::PAGINATION_PER_PAGE);
    }
}
