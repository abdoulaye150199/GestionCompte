<?php

namespace App\Exceptions;

class CompteNotFoundException extends ApiException
{
    public function __construct(string $compteId)
    {
        parent::__construct(
            'Le compte avec l\'ID spécifié n\'existe pas',
            404,
            [
                'code' => 'COMPTE_NOT_FOUND',
                'details' => [
                    'compteId' => $compteId
                ]
            ]
        );
    }
}