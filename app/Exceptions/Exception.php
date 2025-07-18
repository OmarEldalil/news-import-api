<?php

namespace App\Exceptions;

use App\Constants\Errors;

class Exception extends \Exception
{
    private mixed $httpCode;

    public function __construct($errorCode)
    {
        $this->httpCode = Errors::HTTP_STATUS_CODES[$errorCode];
        $this->message = Errors::MESSAGES[$errorCode];
        parent::__construct(Errors::MESSAGES[$errorCode], $errorCode);
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpCode;
    }

}
