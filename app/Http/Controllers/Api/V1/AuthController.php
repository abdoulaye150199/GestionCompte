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
     *     path="/abdoulaye.diallo/api/v1/login",
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

        $user = User::where('login', $validated['login'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return $this->errorResponse('Identifiants invalides', 401);
        }

        $token = $user->createToken('API Token')->accessToken;

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
     *     path="/abdoulaye.diallo/api/v1/logout",
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
     *     path="/abdoulaye.diallo/api/v1/user",
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
}
