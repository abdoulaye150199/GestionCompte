<?php

namespace App\Exceptions;

use App\Traits\RestResponse;
use Illuminate\Http\JsonResponse;

class AuthenticationException extends ApiException
{
    public function __construct(string $message = 'Authentification requise')
    {
        parent::__construct($message, 401, [
            'code' => 'AUTHENTICATION_REQUIRED',
            'details' => [
                'message' => 'Vous devez être authentifié pour accéder à cette ressource'
            ]
        ]);
    }

    public function render(): JsonResponse
    {
        return $this->errorResponse($this->message, $this->statusCode, $this->errors);
    }
}
