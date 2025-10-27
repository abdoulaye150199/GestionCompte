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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Artisan;

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
     *         description="Identifiants invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Identifiants invalides")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Vérifier la connexion à la base de données
            try {
                DB::connection()->getPdo();
            } catch (\Exception $e) {
                Log::error('Database connection failed: ' . $e->getMessage());
                return $this->errorResponse('Erreur de connexion à la base de données', 500);
            }

            $credentials = $request->validated();

            // Rechercher l'utilisateur par login
            $user = User::where('login', $credentials['login'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                Log::warning('Failed login attempt for login: ' . $credentials['login']);
                return $this->errorResponse('Identifiants invalides', 401);
            }

            try {
                // Vérifier si les clients Passport existent
                $clientExists = DB::table('oauth_clients')->where('personal_access_client', 1)->exists();
                if (!$clientExists) {
                    Log::error('No Passport clients found. Running passport:install...');
                    \Artisan::call('passport:install', ['--force' => true]);
                }

                // Créer un Personal Access Token
                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->accessToken;

                if (!$token) {
                    throw new \Exception('Token generation failed');
                }

                // Structurer la réponse
                return $this->successResponse([
                    'user' => [
                        'id' => $user->id,
                        'login' => $user->login,
                        'type' => $user->type ?? 'client',
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_at' => $tokenResult->token->expires_at,
                ], 'Connexion réussie');

            } catch (\Exception $e) {
                Log::error('Token creation error: ' . $e->getMessage());
                if (str_contains($e->getMessage(), 'Client authentication failed')) {
                    return $this->errorResponse('Erreur de configuration OAuth. Contactez l\'administrateur.', 500);
                }
                return $this->errorResponse('Erreur lors de la création du token: ' . $e->getMessage(), 500);
            }
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return $this->errorResponse('Erreur serveur: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/register",
     *     summary="Inscription d'un nouveau client",
     *     description="Crée un nouveau compte client",
     *     operationId="register",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"login", "password", "nom", "nci", "email", "telephone", "adresse"},
     *             @OA\Property(property="login", type="string", description="Login unique"),
     *             @OA\Property(property="password", type="string", format="password", description="Mot de passe"),
     *             @OA\Property(property="nom", type="string", description="Nom complet"),
     *             @OA\Property(property="nci", type="string", description="Numéro de carte d'identité"),
     *             @OA\Property(property="email", type="string", format="email", description="Adresse email"),
     *             @OA\Property(property="telephone", type="string", description="Numéro de téléphone"),
     *             @OA\Property(property="adresse", type="string", description="Adresse complète")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inscription réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inscription réussie"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="token", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'login' => $validated['login'],
            'password' => Hash::make($validated['password']),
        ]);

        // Créer le profil client
        $user->client()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'nom' => $validated['nom'],
            'nci' => $validated['nci'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'],
            'adresse' => $validated['adresse'],
        ]);

        $token = $user->createToken('API Token')->accessToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'login' => $user->login,
                'type' => $user->type,
            ],
            'token' => $token,
        ], 'Inscription réussie', 201);
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     summary="Déconnexion",
     *     description="Révoque le token d'accès actuel",
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
        $user = $request->user();
        $user->token()->revoke();

        return $this->successResponse(null, 'Déconnexion réussie');
    }
}
