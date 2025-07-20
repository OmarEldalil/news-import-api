<?php

namespace App\Exceptions;

use App\Constants\Errors;
use Symfony\Component\HttpFoundation\Response;

class Exception extends \Exception
{
    /**
     * @var int
     */
    private int $httpCode;
    private ?array $payload;

    public function __construct(int $code, ?string $customMessage = null, ?array $payload = null)
    {
        $this->httpCode = !empty(Errors::HTTP_STATUS_CODES[$code]) ? Errors::HTTP_STATUS_CODES[$code] : Response::HTTP_INTERNAL_SERVER_ERROR;
        $this->message = !empty($customMessage) ? $customMessage : (!empty(Errors::MESSAGES[$code]) ? Errors::MESSAGES[$code] : "An error occurred. Please try again later.");
        $this->payload = $payload;
        parent::__construct($this->message, $code);
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }
}
