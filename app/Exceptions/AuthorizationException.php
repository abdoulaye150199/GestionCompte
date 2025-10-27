<?php

namespace App\Exceptions;

use App\Traits\RestResponse;
use Illuminate\Http\JsonResponse;

class AuthorizationException extends ApiException
{
    public function __construct(string $message = 'Accès non autorisé')
    {
        parent::__construct($message, 403, [
            'code' => 'AUTHORIZATION_DENIED',
            'details' => [
                'message' => 'Vous n\'avez pas les permissions nécessaires pour accéder à cette ressource'
            ]
        ]);
    }

    public function render(): JsonResponse
    {
        return $this->errorResponse($this->message, $this->statusCode, $this->errors);
    }
}
