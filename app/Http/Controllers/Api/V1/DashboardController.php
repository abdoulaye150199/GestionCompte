<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TransactionService;

class DashboardController extends Controller
{
    protected $service;

    public function __construct(TransactionService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard",
     *     summary="Obtenir les statistiques globales",
     *     description="Récupère les statistiques globales du système (réservé aux administrateurs)",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques récupérées avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="total_accounts", type="integer", example=150),
     *             @OA\Property(property="active_accounts", type="integer", example=120),
     *             @OA\Property(property="total_transactions", type="integer", example=1000),
     *             @OA\Property(property="total_amount", type="number", format="float", example=5000000.00)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié"),
     *     @OA\Response(response=403, description="Non autorisé - Accès admin requis")
     * )
     */
    public function global(Request $request)
    {
        return response()->json($this->service->globalDashboard());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/me",
     *     summary="Obtenir mes statistiques personnelles",
     *     description="Récupère les statistiques personnelles de l'utilisateur connecté",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques récupérées avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="my_accounts", type="integer", example=3),
     *             @OA\Property(property="total_balance", type="number", format="float", example=150000.00),
     *             @OA\Property(property="monthly_transactions", type="integer", example=25),
     *             @OA\Property(property="last_transaction_date", type="string", format="date-time", example="2025-11-02T10:00:00Z")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function me(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json($this->service->personalDashboard($user));
    }
}
