<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\RestResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    use RestResponse;

    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Connexion utilisateur",
     *     description="Authentifie un utilisateur et retourne un token d'accès",
     *     operationId="login",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"login", "password"},
     *             @OA\Property(property="login", type="string", description="Login de l'utilisateur"),
     *             @OA\Property(property="password", type="string", format="password", description="Mot de passe")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Connexion réussie"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="login", type="string"),
     *                     @OA\Property(property="type", type="string", enum={"admin", "client"})
     *                 ),
     *                 @OA\Property(property="token", type="string", description="Token d'accès Bearer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants invalides"
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $user = User::where('login', $validated['login'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return $this->errorResponse('Identifiants invalides', 401);
            }

            try {
                $token = $user->createToken('API Token')->accessToken;
            } catch (\Exception $e) {
                Log::error('Erreur lors de la création du token: ' . $e->getMessage());
                return $this->errorResponse('Erreur lors de la génération du token d\'accès', 500);
            }

            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'login' => $user->login,
                    'type' => $user->type,
                    'nom' => $user->client?->nom ?? $user->admin?->nom ?? null,
                    'email' => $user->client?->email ?? $user->admin?->email ?? null,
                    'telephone' => $user->client?->telephone ?? $user->admin?->telephone ?? null,
                ],
                'token' => $token,
            ], 'Connexion réussie');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la connexion: ' . $e->getMessage());
            return $this->errorResponse('Une erreur est survenue lors de la connexion', 500);
        }

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'login' => $user->login,
                'type' => $user->type,
                'nom' => $user->client?->nom ?? $user->admin?->nom ?? null,
                'email' => $user->client?->email ?? $user->admin?->email ?? null,
                'telephone' => $user->client?->telephone ?? $user->admin?->telephone ?? null,
            ],
            'token' => $token,
        ], 'Connexion réussie');
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     summary="Déconnexion utilisateur",
     *     description="Révoque le token d'accès de l'utilisateur",
     *     operationId="logout",
     *     tags={"Authentification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie")
     *         )
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();
        
        return $this->successResponse(null, 'Déconnexion réussie');
    }

    /**
     * @OA\Get(
     *     path="/user",
     *     summary="Informations de l'utilisateur",
     *     description="Retourne les informations de l'utilisateur connecté",
     *     operationId="getAuthenticatedUser",
     *     tags={"Authentification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informations de l'utilisateur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="login", type="string"),
     *                     @OA\Property(property="type", type="string", enum={"admin", "client"})
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    /**
     * @OA\Post(
     *     path="/register",
     *     summary="Inscription utilisateur",
     *     description="Crée un nouveau compte utilisateur client",
     *     operationId="register",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"login", "password", "nom", "nci", "email", "telephone", "adresse"},
     *             @OA\Property(property="login", type="string", description="Login de l'utilisateur"),
     *             @OA\Property(property="password", type="string", format="password", description="Mot de passe"),
     *             @OA\Property(property="nom", type="string", description="Nom complet"),
     *             @OA\Property(property="nci", type="string", description="Numéro CNI"),
     *             @OA\Property(property="email", type="string", format="email", description="Adresse email"),
     *             @OA\Property(property="telephone", type="string", description="Numéro de téléphone"),
     *             @OA\Property(property="adresse", type="string", description="Adresse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Utilisateur créé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="login", type="string"),
     *                     @OA\Property(property="type", type="string", enum={"client"})
     *                 ),
     *                 @OA\Property(property="token", type="string", description="Token d'accès Bearer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation"
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Créer l'utilisateur
        $user = User::create([
            'id' => (string) Str::uuid(),
            'login' => $validated['login'],
            'password' => Hash::make($validated['password']),
            'type' => 'client',
        ]);

        // Créer le profil client
        $user->client()->create([
            'id' => (string) Str::uuid(),
            'nom' => $validated['nom'],
            'nci' => $validated['nci'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'],
            'adresse' => $validated['adresse'],
        ]);

        // Générer le token
        $token = $user->createToken('API Token')->accessToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'login' => $user->login,
                'type' => $user->type,
                'nom' => $user->client->nom,
                'email' => $user->client->email,
                'telephone' => $user->client->telephone,
            ],
            'token' => $token,
        ], 'Utilisateur créé avec succès', 201);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'login' => $user->login,
                'type' => $user->type,
                'nom' => $user->client?->nom ?? $user->admin?->nom ?? null,
                'email' => $user->client?->email ?? $user->admin?->email ?? null,
                'telephone' => $user->client?->telephone ?? $user->admin?->telephone ?? null,
            ]
        ]);
    }
}
