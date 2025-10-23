<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="API Gestion de Compte",
 *     description="API pour la gestion des comptes bancaires"
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     description="Connexion avec token Bearer JWT",
 *     name="Bearer",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="bearerAuth"
 * )
 */
class BankAccountController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/accounts",
     *     tags={"Comptes"},
     *     summary="Liste tous les comptes",
     *     description="Retourne la liste de tous les comptes bancaires",
     *     operationId="getAllAccounts",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="account_number", type="string", example="FR7630001007941234567890185"),
     *                     @OA\Property(property="balance", type="number", format="float", example=1000.50),
     *                     @OA\Property(property="client_id", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $accounts = BankAccount::with('client')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $accounts->map(function($account) {
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
            })
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/accounts/{id}",
     *     tags={"Comptes"},
     *     summary="Obtenir un compte spécifique",
     *     description="Retourne les détails d'un compte bancaire",
     *     operationId="getAccountById",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du compte",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="account_number", type="string", example="FR7630001007941234567890185"),
     *                 @OA\Property(property="balance", type="number", format="float", example=1000.50),
     *                 @OA\Property(property="client_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé"
     *     )
     * )
     */
    public function show($id)
    {
        $account = BankAccount::with('client')->find($id);
        
        if (!$account) {
            return response()->json([
                'status' => 'error',
                'message' => 'Compte non trouvé'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $account
        ]);
    }
}