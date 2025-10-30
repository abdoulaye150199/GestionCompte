<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Compte;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Traits\Validators\ValidationTrait;

class AccountController extends Controller
{
    use ValidationTrait;

    public function store(Request $request)
    {
        $payload = $request->all();
        $errors = $this->validateAccountStorePayload($payload);
        if (!empty($errors)) {
            return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR','message' => 'Les données fournies sont invalides','details' => $errors]], 400);
        }

        try {
            // Extract client data
            $clientData = $payload['client'];

            // If the caller provided an existing client id, reuse that client
            if (array_key_exists('id', $clientData) && !is_null($clientData['id']) && $clientData['id'] !== '') {
                $client = Client::where('id', $clientData['id'])->first();
                if (! $client) {
                    return response()->json(['success' => false, 'error' => ['code' => 'VALIDATION_ERROR','message' => 'Les données fournies sont invalides','details' => ['client.id' => 'Client.id invalide.']]], 400);
                }

                // Determine titulaire
                $titulaire = $clientData['titulaire'] ?? trim(($client->nom ?? '') . ' ' . ($client->prenom ?? ''));
                if (empty($titulaire)) {
                    $titulaire = $client->user->nom . ' ' . $client->user->prenom ?? 'Titulaire';
                }

                // Build compte data and create Compte for existing client
                $comptePayload = [
                    'id' => (string) Str::uuid(),
                    'client_id' => (string) $client->id,
                    'user_id' => isset($client->user_id) ? (string) $client->user_id : null,
                    'titulaire_compte' => $titulaire,
                    'type_compte' => $payload['type'],
                    'solde' => $payload['soldeInitial'],
                    'devise' => $payload['devise'],
                    'statut_compte' => 'actif',
                    'date_creation' => now(),
                ];

                $compte = Compte::create($comptePayload);

                return response()->json([
                    'success' => true,
                    'message' => 'Compte créé pour client existant',
                    'data' => [
                        'id' => (string) $compte->id,
                        'numeroCompte' => $compte->numero_compte,
                        'titulaire' => $titulaire,
                        'type' => $compte->type_compte,
                        'solde' => $compte->solde,
                        'devise' => $compte->devise,
                        'dateCreation' => $compte->created_at->toIso8601String(),
                        'statut' => $compte->statut_compte,
                        'metadata' => [
                            'derniereModification' => $compte->updated_at->toIso8601String(),
                            'version' => $compte->version ?? 1,
                        ],
                    ]
                ], 201);
            }
            // Split titulaire into nom prenom, assume "Prenom Nom"
            $parts = explode(' ', $clientData['titulaire'], 2);
            $prenom = $parts[0] ?? '';
            $nom = $parts[1] ?? $prenom;

            $userData = [
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $clientData['email'],
                'telephone' => $clientData['telephone'],
                'adresse' => $clientData['adresse'],
                'date_naissance' => null, // not provided
                'nci' => $clientData['nci'],
            ];

            $compteData = [
                'type_compte' => $payload['type'],
                'solde' => $payload['soldeInitial'], // use soldeInitial as initial solde
                'devise' => $payload['devise'],
                'statut_compte' => 'actif',
                'date_creation' => now(),
            ];

            $user = User::createAccount($userData, $compteData);

            $compte = $user->client->comptes()->first(); // assume one compte

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès',
                'data' => [
                    'id' => (string) $compte->id,
                    'numeroCompte' => $compte->numero_compte,
                    'titulaire' => $clientData['titulaire'],
                    'type' => $compte->type_compte,
                    'solde' => $compte->solde,
                    'devise' => $compte->devise,
                    'dateCreation' => $compte->created_at->toIso8601String(),
                    'statut' => $compte->statut_compte,
                    'metadata' => [
                        'derniereModification' => $compte->updated_at->toIso8601String(),
                        'version' => $compte->version ?? 1,
                    ],
                ]
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Account creation failed: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Les données fournies sont invalides',
                    'details' => ['general' => $e->getMessage()],
                ],
            ], 400);
        }
    }
}
