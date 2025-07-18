<?php

namespace App\Constants;

use Symfony\Component\HttpFoundation\Response;

class Errors
{

    const GENERAL_ERROR = 'general_error';
    const VALIDATION_ERROR = 'validation_error';

    const MESSAGES = [
        self::GENERAL_ERROR => "An unexpected error occurred. Please try again later.",
        self::VALIDATION_ERROR => "Validation failed. Please check your input and try again.",
    ];
    const HTTP_STATUS_CODES = [
        self::GENERAL_ERROR => Response::HTTP_INTERNAL_SERVER_ERROR,
        self::VALIDATION_ERROR => Response::HTTP_UNPROCESSABLE_ENTITY,
    ];

}
