<?php

namespace App\Exceptions;

use App\Traits\RestResponse;
use Illuminate\Http\JsonResponse;

class ValidationException extends ApiException
{
    protected $validationErrors;

    public function __construct(array $errors, string $message = 'Erreur de validation')
    {
        $this->validationErrors = $errors;
        parent::__construct($message, 422, [
            'code' => 'VALIDATION_ERROR',
            'details' => $errors
        ]);
    }

    public function render(): JsonResponse
    {
        return $this->errorResponse($this->message, $this->statusCode, $this->errors);
    }
}
