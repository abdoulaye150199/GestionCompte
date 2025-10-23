<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Client;
use App\Http\Requests\StoreBankAccountRequest;
use App\Http\Requests\UpdateBankAccountRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    /**
     * Liste tous les comptes bancaires
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
        $page = $request->input('page', 1);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Crée un nouveau compte bancaire
     */
    public function store(StoreBankAccountRequest $request): JsonResponse
    {
        $account = BankAccount::create($request->validated());
        return response()->json($account, Response::HTTP_CREATED);
    }

    /**
     * Affiche les détails d'un compte bancaire
     */
    public function show(BankAccount $bankAccount): JsonResponse
    {
        $bankAccount->load('client');
        return response()->json($bankAccount);
    }

    /**
     * Met à jour un compte bancaire
     */
    public function update(UpdateBankAccountRequest $request, BankAccount $bankAccount): JsonResponse
    {
        $bankAccount->update($request->validated());
        return response()->json($bankAccount);
    }

    /**
     * Supprime un compte bancaire
     */
    public function destroy(BankAccount $bankAccount): JsonResponse
    {
        $bankAccount->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Effectue un dépôt sur un compte
     */
    public function deposit(BankAccount $bankAccount, StoreBankAccountRequest $request): JsonResponse
    {
        $amount = $request->validated()['amount'];
        $bankAccount->balance += $amount;
        $bankAccount->save();

        return response()->json([
            'message' => 'Dépôt effectué avec succès',
            'new_balance' => $bankAccount->balance
        ]);
    }

    /**
     * Effectue un retrait sur un compte
     */
    public function withdraw(BankAccount $bankAccount, StoreBankAccountRequest $request): JsonResponse
    {
        $amount = $request->validated()['amount'];

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