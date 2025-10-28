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
use Laravel\Passport\Client;

class AuthController extends Controller
{
    use RestResponse;

    public function __construct()
    {
        Log::info('AuthController initialized');
    }

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
     *         description="Connexion réussie"
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
            Log::info('Tentative de connexion', ['login' => $request->login]);
            
            $user = User::where('login', $request->login)->first();
            
            if (!$user || !Hash::check($request->password, $user->password)) {
                Log::warning('Échec de connexion : identifiants invalides', ['login' => $request->login]);
                return $this->error('Login ou mot de passe incorrect', 401);
            }

            // Get the password grant client
            $client = Client::where('password_client', true)->first();
            Log::info('État des clients Passport', ['clientExists' => (bool)$client]);
            
            if (!$client) {
                Log::error('Aucun client password grant trouvé. Veuillez exécuter php artisan passport:install');
                return $this->error('Erreur de configuration du serveur', 500);
            }

            Log::info('Tentative de création du token pour l\'utilisateur', [
                'user_id' => $user->id,
                'client_id' => $client->id
            ]);
            
            $token = $user->createToken($user->login)->accessToken;
            Log::info('Token créé avec succès');

            return $this->success([
                'user' => [
                    'id' => $user->id,
                    'login' => $user->login,
                    'type' => $user->type
                ],
                'token' => $token
            ], 'Connexion réussie');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la connexion', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error('Erreur lors de la connexion: ' . $e->getMessage(), 500);
        }
    }

    // ... rest of the controller methods ...
}