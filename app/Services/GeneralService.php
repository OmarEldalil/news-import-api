<?php

namespace App\Services;

use App\Constants\Errors;

class GeneralService
{
    public function respondWithSuccess(array $data, string $message, int $statusCode = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $statusCode);
    }

    public function respondWithValidationErrors(array $errors): \Illuminate\Http\JsonResponse
    {
        return $this->respondWithErrors($errors, Errors::VALIDATION_ERROR, 422);
    }

    public function respondWithErrors(array $errors, string $code = Errors::GENERAL_ERROR, int $statusCode = 400): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => $code,
                'errors' => $errors,
            ],
        ], $statusCode);
    }
}
