<?php

namespace App\Exceptions;

class UserNotFoundException extends ApiException
{
    public function __construct(string $userId)
    {
        parent::__construct(
            'L\'utilisateur avec l\'ID spécifié n\'existe pas',
            404,
            [
                'code' => 'USER_NOT_FOUND',
                'details' => [
                    'userId' => $userId
                ]
            ]
        );
    }
}