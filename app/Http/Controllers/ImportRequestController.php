<?php

namespace App\Http\Controllers;

use App\Exceptions\CSVFileException;
use App\Exceptions\ImportRequestException;
use App\Exceptions\StorageException;
use App\Http\Requests\GetImportRequestRequest;
use App\Http\Requests\StoreImportRequestRequest;
use App\Services\GeneralService;
use App\Services\ImportRequestService;
use Illuminate\Http\JsonResponse;

class ImportRequestController extends Controller
{
    private ImportRequestService $importRequestService;

    public function __construct(GeneralService $generalService, ImportRequestService $importRequestService)
    {
        parent::__construct($generalService);
        $this->importRequestService = $importRequestService;
    }

    /**
     * @throws CSVFileException
     * @throws StorageException
     */
    public function store(StoreImportRequestRequest $request): JsonResponse
    {
        $data = $this->importRequestService->storeImportRequest($request->file('news_file'));

        return $this->generalService->respondWithSuccess($data->toArray(), "Import request created successfully.", 201);
    }

    /**
     * @throws ImportRequestException
     */
    public function get(GetImportRequestRequest $request): JsonResponse
    {
        $data = $this->importRequestService->getImportRequests($request->input('id'), $request->input('status'));
        return $this->generalService->respondWithSuccess($data->toArray());

    }
}
