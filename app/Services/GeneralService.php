<?php

namespace App\Services;

use App\Constants\Errors;

class GeneralService
{
    public function respondWithSuccess(array $data, ?string $message = null, int $statusCode = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            ...(!empty($message) ? ['message' => $message] : []),
        ], $statusCode);
    }

    public function respondWithErrors(string $message, ?array $errors = null, string $code = Errors::GENERAL_ERROR, int $statusCode = 400): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                ...(!empty($errors) ? ['errors' => $errors] : []),
            ],
        ], $statusCode);
    }
}
