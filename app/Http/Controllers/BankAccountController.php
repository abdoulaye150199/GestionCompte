<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Client;
use App\Http\Requests\StoreBankAccountRequest;
use App\Http\Requests\UpdateBankAccountRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Comptes Bancaires",
 *     description="Gestion des comptes bancaires"
 * )
 */
class BankAccountController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/bank-accounts",
     *     summary="Liste tous les comptes bancaires",
     *     description="Récupère la liste des comptes bancaires avec filtrage, recherche et pagination",
     *     operationId="getBankAccounts",
     *     tags={"Comptes Bancaires"},
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type de compte",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Rechercher par numéro de compte ou nom du titulaire",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Champ de tri",
     *         @OA\Schema(type="string", default="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordre de tri",
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         @OA\Schema(type="integer", default=10, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes bancaires récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Bank accounts retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/BankAccount")),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = BankAccount::query()
            ->where('deleted_at', null);

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('statut')) {
            $query->where('status', $request->statut);
        }

        // Search by holder or account number
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortField = $request->input('sort', 'created_at');
        $sortOrder = $request->input('order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        // Pagination
        $perPage = min($request->input('limit', 10), 100);
        $page = $request->input('page', 1);
        $accounts = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'message' => 'Bank accounts retrieved successfully',
            'data' => $accounts->items(),
            'meta' => [
                'total' => $accounts->total(),
                'current_page' => $accounts->currentPage(),
                'per_page' => $accounts->perPage(),
                'last_page' => $accounts->lastPage(),
                'first_item' => $accounts->firstItem(),
                'last_item' => $accounts->lastItem()
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/bank-accounts",
     *     summary="Crée un nouveau compte bancaire",
     *     description="Crée un nouveau compte bancaire avec les informations fournies",
     *     operationId="createBankAccount",
     *     tags={"Comptes Bancaires"},
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreBankAccountRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte bancaire créé avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/BankAccount")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données invalides"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function store(StoreBankAccountRequest $request): JsonResponse
    {
        $account = BankAccount::create($request->validated());
        return response()->json($account, Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/bank-accounts/{bankAccount}",
     *     summary="Affiche les détails d'un compte bancaire",
     *     description="Récupère les détails d'un compte bancaire spécifique avec les informations du client",
     *     operationId="getBankAccount",
     *     tags={"Comptes Bancaires"},
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="bankAccount",
     *         in="path",
     *         required=true,
     *         description="ID du compte bancaire",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du compte bancaire récupérés avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/BankAccount")
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
    public function show(BankAccount $bankAccount): JsonResponse
    {
        $bankAccount->load('client');
        return response()->json($bankAccount);
    }

    /**
     * @OA\Put(
     *     path="/api/bank-accounts/{bankAccount}",
     *     summary="Met à jour un compte bancaire",
     *     description="Met à jour les informations d'un compte bancaire existant",
     *     operationId="updateBankAccount",
     *     tags={"Comptes Bancaires"},
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="bankAccount",
     *         in="path",
     *         required=true,
     *         description="ID du compte bancaire",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateBankAccountRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte bancaire mis à jour avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/BankAccount")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte bancaire non trouvé"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données invalides"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function update(UpdateBankAccountRequest $request, BankAccount $bankAccount): JsonResponse
    {
        $bankAccount->update($request->validated());
        return response()->json($bankAccount);
    }

    /**
     * @OA\Delete(
     *     path="/api/bank-accounts/{bankAccount}",
     *     summary="Supprime un compte bancaire",
     *     description="Supprime un compte bancaire existant",
     *     operationId="deleteBankAccount",
     *     tags={"Comptes Bancaires"},
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="bankAccount",
     *         in="path",
     *         required=true,
     *         description="ID du compte bancaire",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Compte bancaire supprimé avec succès"
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
    public function destroy(BankAccount $bankAccount): JsonResponse
    {
        $bankAccount->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Post(
     *     path="/api/bank-accounts/{bankAccount}/deposit",
     *     summary="Effectue un dépôt sur un compte",
     *     description="Ajoute un montant au solde du compte bancaire",
     *     operationId="depositBankAccount",
     *     tags={"Comptes Bancaires"},
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="bankAccount",
     *         in="path",
     *         required=true,
     *         description="ID du compte bancaire",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", format="float", description="Montant à déposer", example=100.50)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dépôt effectué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dépôt effectué avec succès"),
     *             @OA\Property(property="new_balance", type="number", format="float", example=150.75)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte bancaire non trouvé"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données invalides"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function deposit(BankAccount $bankAccount, Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01'
        ]);

        $amount = $request->amount;
        $bankAccount->balance += $amount;
        $bankAccount->save();

        return response()->json([
            'message' => 'Dépôt effectué avec succès',
            'new_balance' => $bankAccount->balance
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/bank-accounts/{bankAccount}/withdraw",
     *     summary="Effectue un retrait sur un compte",
     *     description="Retire un montant du solde du compte bancaire",
     *     operationId="withdrawBankAccount",
     *     tags={"Comptes Bancaires"},
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="bankAccount",
     *         in="path",
     *         required=true,
     *         description="ID du compte bancaire",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", format="float", description="Montant à retirer", example=50.25)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Retrait effectué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Retrait effectué avec succès"),
     *             @OA\Property(property="new_balance", type="number", format="float", example=100.50)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Solde insuffisant",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Solde insuffisant")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte bancaire non trouvé"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données invalides"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function withdraw(BankAccount $bankAccount, Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01'
        ]);

        $amount = $request->amount;

        if ($bankAccount->balance < $amount) {
            return response()->json([
                'message' => 'Solde insuffisant'
            ], Response::HTTP_BAD_REQUEST);
        }

        $bankAccount->balance -= $amount;
        $bankAccount->save();

        return response()->json([
            'message' => 'Retrait effectué avec succès',
            'new_balance' => $bankAccount->balance
        ]);
    }
}
