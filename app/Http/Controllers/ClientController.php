<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * @OA\Tag(
 *     name="Clients",
 *     description="Gestion des clients"
 * )
 */
class ClientController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/clients",
     *     summary="Liste tous les clients",
     *     description="Récupère la liste de tous les clients avec leurs comptes bancaires associés",
     *     operationId="getClients",
     *     tags={"Clients"},
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des clients récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Client")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $clients = Client::with('bankAccounts')->get();
        return response()->json($clients);
    }

    /**
     * @OA\Post(
     *     path="/api/clients",
     *     summary="Crée un nouveau client",
     *     description="Crée un nouveau client avec les informations fournies",
     *     operationId="createClient",
     *     tags={"Clients"},
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreClientRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Client créé avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Client")
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
    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = Client::create($request->validated());
        return response()->json($client, Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/clients/{client}",
     *     summary="Affiche les détails d'un client",
     *     description="Récupère les détails d'un client spécifique avec ses comptes bancaires",
     *     operationId="getClient",
     *     tags={"Clients"},
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="client",
     *         in="path",
     *         required=true,
     *         description="ID du client",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du client récupérés avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Client")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function show(Client $client): JsonResponse
    {
        $client->load('bankAccounts');
        return response()->json($client);
    }

    /**
     * @OA\Put(
     *     path="/api/clients/{client}",
     *     summary="Met à jour un client",
     *     description="Met à jour les informations d'un client existant",
     *     operationId="updateClient",
     *     tags={"Clients"},
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="client",
     *         in="path",
     *         required=true,
     *         description="ID du client",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateClientRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client mis à jour avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Client")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé"
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
    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $client->update($request->validated());
        return response()->json($client);
    }

    /**
     * @OA\Delete(
     *     path="/api/clients/{client}",
     *     summary="Supprime un client",
     *     description="Supprime un client existant",
     *     operationId="deleteClient",
     *     tags={"Clients"},
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="client",
     *         in="path",
     *         required=true,
     *         description="ID du client",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Client supprimé avec succès"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function destroy(Client $client): JsonResponse
    {
        $client->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
