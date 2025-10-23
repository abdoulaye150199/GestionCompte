<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

class ApiException extends Exception
{
    use ApiResponse;

    protected $statusCode;
    protected $errors;

    public function __construct(string $message = 'An error occurred', int $statusCode = 400, $errors = null)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    public function render(): JsonResponse
    {
        return $this->errorResponse($this->message, $this->statusCode, $this->errors);
    }
}