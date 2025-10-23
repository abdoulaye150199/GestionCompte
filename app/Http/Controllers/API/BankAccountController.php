<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Comptes",
 *     description="Endpoints pour la gestion des comptes bancaires"
 * )
 */
class BankAccountController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/accounts",
     *     summary="Liste tous les comptes bancaires",
     *     description="Retourne la liste de tous les comptes bancaires",
     *     operationId="listAccounts",
     *     tags={"Comptes"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
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
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => BankAccount::with('client')->get()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/accounts/{id}",
     *     summary="Détails d'un compte bancaire",
     *     description="Retourne les détails d'un compte bancaire spécifique",
     *     operationId="getAccount",
     *     tags={"Comptes"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du compte bancaire",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte bancaire trouvé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="account_number", type="string", example="FR7630001007941234567890185"),
     *                 @OA\Property(property="balance", type="number", format="float", example=1000.50),
     *                 @OA\Property(property="client_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="client",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte bancaire non trouvé"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function show($id)
    {
        $account = BankAccount::with('client')->find($id);
        
        if (!$account) {
            return response()->json([
                'status' => 'error',
                'message' => 'Compte bancaire non trouvé'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $account
        ]);
    }
}