<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Traits\RestResponse;
use App\Transformers\BankAccountTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Comptes",
 *     description="Opérations sur les comptes bancaires"
 * )
 * @OA\Get(
 *     path="/api/v1/comptes",
 *     tags={"Comptes"},
 *     summary="Liste tous les comptes bancaires",
 *     description="Retourne la liste de tous les comptes bancaires de la base de données",
 *     operationId="getAllComptes",
 *     security={},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des comptes récupérée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Liste des comptes récupérée avec succès"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="string", example="019a1307-a448-70c8-83a2-0c8d9feb6c3d"),
 *                     @OA\Property(property="account_number", type="string", example="ACC-634716"),
 *                     @OA\Property(property="balance", type="number", format="float", example=882.48),
 *                     @OA\Property(property="type", type="string", example="savings"),
 *                     @OA\Property(
 *                         property="client",
 *                         type="object",
 *                         @OA\Property(property="id", type="string", example="019a1307-9d9f-725c-b77f-149264708e38"),
 *                         @OA\Property(property="name", type="string", example="John Doe")
 *                     ),
 *                     @OA\Property(property="created_at", type="string", format="date-time"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
class BankAccountController extends Controller
{
    use RestResponse;
    /**
     * @OA\Get(
     *     path="/v1/accounts",
     *     tags={"Comptes"},
     *     summary="Liste tous les comptes",
     *     description="Retourne la liste de tous les comptes bancaires",
     *     operationId="getAllAccounts",
     *     security={},
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="019a1307-a448-70c8-83a2-0c8d9feb6c3d"),
     *                     @OA\Property(property="account_number", type="string", example="ACC-634716"),
     *                     @OA\Property(property="balance", type="number", format="float", example=882.48),
     *                     @OA\Property(property="type", type="string", example="savings"),
     *                     @OA\Property(property="client_id", type="string", example="019a1307-9d9f-725c-b77f-149264708e38"),
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
        try {
            $accounts = BankAccount::with('client')->get();
            
            // Si aucun compte n'est trouvé, retourner un tableau vide plutôt qu'une erreur
            if ($accounts->isEmpty()) {
                return $this->successResponse([], 'Aucun compte trouvé');
            }
            
            $transformedAccounts = $this->formatCollection($accounts, [BankAccountTransformer::class, 'transform']);
            return $this->successResponse($transformedAccounts, 'Liste des comptes récupérée avec succès');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération des comptes: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération des comptes',
                'data' => [],
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
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
            return $this->errorResponse('Compte non trouvé', 404);
        }

        $transformedAccount = BankAccountTransformer::transform($account);
        return $this->successResponse($transformedAccount, 'Détails du compte récupérés avec succès');
    }
}