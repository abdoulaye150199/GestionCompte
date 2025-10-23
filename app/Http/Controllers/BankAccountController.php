<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Client;
use App\Http\Requests\StoreBankAccountRequest;
use App\Http\Requests\UpdateBankAccountRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BankAccountController extends Controller
{
    /**
     * Liste tous les comptes bancaires
     */
    public function index(): JsonResponse
    {
        $accounts = BankAccount::with('client')->get();
        return response()->json($accounts);
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