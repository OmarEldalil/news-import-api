<?php

namespace App\Exceptions;

use App\Constants\Errors;
use App\Services\GeneralService;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Handler
{
    /**
     * Configure exception handling for the application
     */
    public static function configure(Exceptions $exceptions): void
    {
        self::configureKnownExceptions($exceptions);
        self::configureSystemExceptions($exceptions);
    }

    protected static function configureKnownExceptions(Exceptions $exceptions): void
    {
        $exceptions->render(function (Exception $e) {
            $generalService = new GeneralService();

            if ($e->getHttpCode() > 499) {
                Log::error('Unhandled Exception', [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            return $generalService->respondWithErrors(
                $e->getMessage(),
                $e->getPayload(),
                $e->getCode(),
                $e->getHttpCode()
            );
        });

        $exceptions->render(function (ValidationException $e) {
            $generalService = new GeneralService();
            $validationException = new RequestValidationException(
                Errors::VALIDATION_ERROR,
                $e->getMessage(),
                $e->errors()
            );

            return $generalService->respondWithErrors(
                $validationException->getMessage(),
                $validationException->getPayload(),
                $validationException->getCode(),
                $validationException->getHttpCode()
            );
        });
    }

    protected static function configureSystemExceptions(Exceptions $exceptions): void
    {
        $exceptions->render(function (\Exception $e) {
            $generalService = new GeneralService();

            Log::error('Unexpected Exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (config('app.debug')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            $generalException = new GenericException(Errors::GENERAL_ERROR);

            return $generalService->respondWithErrors(
                $generalException->getMessage(),
                $generalException->getPayload(),
                $generalException->getCode(),
                $generalException->getHttpCode()
            );
        });
    }
}
