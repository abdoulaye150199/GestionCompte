<?php

namespace App\Transformers;

class BankAccountTransformer
{
    /**
     * Transform a bank account
     *
     * @param mixed $account
     * @return array
     */
    public static function transform($account)
    {
        return [
            'id' => $account->id,
            'account_number' => $account->account_number,
            'balance' => $account->balance,
            'type' => $account->type,
            'client' => [
                'id' => $account->client->id,
                'name' => $account->client->name
            ],
            'created_at' => $account->created_at,
            'updated_at' => $account->updated_at
        ];
    }
}