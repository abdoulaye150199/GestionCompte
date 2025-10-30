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
        $validated = $request->validated();

        // accept either 'login' or 'identifier' (validated by LoginRequest)
        $validated = $request->validated();
        $identifier = $validated['login'] ?? $validated['identifier'] ?? null;

        if (empty($identifier)) {
            return $this->errorResponse("Le login ou l'identifiant est requis.", 400);
        }

        $user = User::where('login', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return $this->errorResponse('Identifiants invalides', 401);
        }

    // If the project is using the simple token driver (dev), generate a token
    // and persist it to the users table so TokenGuard can authenticate.
    if (env('API_AUTH_DRIVER', 'token') === 'token') {
        $token = Str::random(60);
        $user->api_token = $token;
        $user->save();
    } else {
        // Passport driver: create a personal access token via Passport
        try {
            $tokenResult = $user->createToken('API Token');
            $token = $tokenResult->accessToken ?? ($tokenResult->plainTextToken ?? null);
        } catch (\Throwable $e) {
            logger()->error('Token creation failed: ' . $e->getMessage());
            return $this->errorResponse('Impossible de générer le token d\'accès', 500);
        }
    }

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'login' => $user->login,
                'type' => $user->type,
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
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'login' => $user->login,
                'type' => $user->type,
            ]
        ]);
    }

    // Helper endpoint for debugging bypassing FormRequest validation
    public function loginRaw(Request $request): JsonResponse
    {
        $identifier = $request->input('login') ?? $request->input('identifier');
        $password = $request->input('password');

        if (empty($identifier)) {
            return $this->errorResponse('login is required', 400);
        }
        if (empty($password)) {
            return $this->errorResponse('password is required', 400);
        }

        $user = User::where('login', $identifier)->orWhere('email', $identifier)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return $this->errorResponse('Identifiants invalides', 401);
        }

        if (env('API_AUTH_DRIVER', 'token') === 'token') {
            $token = Str::random(60);
            $user->api_token = $token;
            $user->save();
        } else {
            try {
                $tokenResult = $user->createToken('API Token');
                $token = $tokenResult->accessToken ?? ($tokenResult->plainTextToken ?? null);
            } catch (\Throwable $e) {
                logger()->error('Token creation failed (loginRaw): ' . $e->getMessage());
                return $this->errorResponse('Impossible de générer le token d\'accès', 500);
            }
        }

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'login' => $user->login,
                'type' => $user->type,
            ],
            'token' => $token,
        ], 'Connexion réussie');
    }
}