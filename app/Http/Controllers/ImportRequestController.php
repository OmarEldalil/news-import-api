<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetImageRequestRequest;
use App\Http\Requests\StoreImageRequestRequest;
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

    public function store(StoreImageRequestRequest $request): JsonResponse
    {
        $data = $this->importRequestService->storeImportRequest($request->file('news_file'));

        return $this->generalService->respondWithSuccess($data->toArray(), "Import request created successfully.");
    }

    public function get(GetImageRequestRequest $request): JsonResponse
    {
        $data = $this->importRequestService->getImportRequests($request->input('id'), $request->input('status'));
        return $this->generalService->respondWithSuccess($data->toArray(), "Import request created successfully.");

    }
}
