<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ClientController extends Controller
{
    /**
     * Liste tous les clients
     */
    public function index(): JsonResponse
    {
        $clients = Client::with('bankAccounts')->get();
        return response()->json($clients);
    }

    /**
     * Crée un nouveau client
     */
    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = Client::create($request->validated());
        return response()->json($client, Response::HTTP_CREATED);
    }

    /**
     * Affiche les détails d'un client
     */
    public function show(Client $client): JsonResponse
    {
        $client->load('bankAccounts');
        return response()->json($client);
    }

    /**
     * Met à jour un client
     */
    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $client->update($request->validated());
        return response()->json($client);
    }

    /**
     * Supprime un client
     */
    public function destroy(Client $client): JsonResponse
    {
        $client->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}