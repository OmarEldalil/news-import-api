<?php

namespace App\Constants;

use Symfony\Component\HttpFoundation\Response;

class Errors
{

    const int GENERAL_ERROR = 1000;
    const int VALIDATION_ERROR = 1001;
    const int NOT_FOUND_ERROR = 1002;
    const int STORAGE_ERROR = 1003;
    const int INVALID_FILE_ERROR = 1004;
    const int UNABLE_TO_OPEN_CSV_ERROR = 1005;
    const int UNABLE_TO_READ_CSV_HEADER_ERROR = 1006;
    const int CSV_HEADER_DOES_NOT_MATCH_ERROR = 1007;
    const int DATABASE_ERROR = 1008;

    const array MESSAGES = [
        self::GENERAL_ERROR => "An unexpected error occurred. Please try again later.",
        self::VALIDATION_ERROR => "Validation failed",
        self::NOT_FOUND_ERROR => "Resource not found.",
        self::STORAGE_ERROR => "Error while storing the file. Please try again later.",
        self::INVALID_FILE_ERROR => "Invalid file",
        self::UNABLE_TO_OPEN_CSV_ERROR => "Unable to open CSV file",
        self::UNABLE_TO_READ_CSV_HEADER_ERROR => "Unable to read CSV headers",
        self::CSV_HEADER_DOES_NOT_MATCH_ERROR => "CSV headers do not match expected format",
        self::DATABASE_ERROR => "Database error occurred. Please try again later.",
    ];
    const array HTTP_STATUS_CODES = [
        self::GENERAL_ERROR => Response::HTTP_INTERNAL_SERVER_ERROR,
        self::VALIDATION_ERROR => Response::HTTP_UNPROCESSABLE_ENTITY,
        self::NOT_FOUND_ERROR => Response::HTTP_NOT_FOUND,
        self::STORAGE_ERROR => Response::HTTP_INTERNAL_SERVER_ERROR,
        self::INVALID_FILE_ERROR => Response::HTTP_UNPROCESSABLE_ENTITY,
        self::UNABLE_TO_OPEN_CSV_ERROR => Response::HTTP_INTERNAL_SERVER_ERROR,
        self::UNABLE_TO_READ_CSV_HEADER_ERROR => Response::HTTP_UNPROCESSABLE_ENTITY,
        self::CSV_HEADER_DOES_NOT_MATCH_ERROR => Response::HTTP_UNPROCESSABLE_ENTITY,
        self::DATABASE_ERROR => Response::HTTP_INTERNAL_SERVER_ERROR,

    ];

}
