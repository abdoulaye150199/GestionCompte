<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompteRequest;
use App\Http\Resources\CompteResource;
use App\Models\Compte;
use App\Traits\RestResponse;
use App\Exceptions\CompteNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class CompteController extends Controller
{
    use RestResponse;

    /**
     * @OA\Get(
     *     path="/comptes",
     *     summary="Lister tous les comptes",
     *     description="Récupère une liste paginée de comptes avec possibilité de filtrage et tri",
     *     operationId="getComptes",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type de compte",
     *         required=false,
     *         @OA\Schema(type="string", enum={"epargne", "cheque"})
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut",
     *         required=false,
     *         @OA\Schema(type="string", enum={"actif", "bloque", "ferme"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par numéro de compte ou nom du titulaire",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Champ de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"dateCreation", "solde", "titulaire"}, default="dateCreation")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordre de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object"),
     *             @OA\Property(property="links", type="object",
     *                 @OA\Property(property="self", type="string"),
     *                 @OA\Property(property="next", type="string"),
     *                 @OA\Property(property="first", type="string"),
     *                 @OA\Property(property="last", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authentification requise",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Authentification requise")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Vérifier les autorisations
        if (!$user) {
            return $this->errorResponse('Authentification requise', 401);
        }

        $validated = $this->validateRequest($request, [
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
            'type' => 'nullable|string|in:epargne,cheque',
            'statut' => 'nullable|string|in:actif,bloque,ferme',
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|string|in:dateCreation,solde,titulaire',
            'order' => 'nullable|string|in:asc,desc',
        ]);

        $query = Compte::with('user')->nonSupprime();

        if ($user->type === 'client') {
            $query->utilisateur($user->id);
        }

        if (isset($validated['type']) && $validated['type']) {
            $query->type($validated['type']);
        }

        if (isset($validated['statut']) && $validated['statut']) {
            $query->statut($validated['statut']);
        }

        if (isset($validated['search']) && $validated['search']) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->numero($search)
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('nom', 'like', "%{$search}%");
                  });
            });
        }

        $sort = $validated['sort'] ?? 'dateCreation';
        $order = $validated['order'] ?? 'desc';

        switch ($sort) {
            case 'dateCreation':
                $query->orderBy('created_at', $order);
                break;
            case 'solde':
                $query->orderBy('solde', $order);
                break;
            case 'titulaire':
                $query->join('users', 'comptes.user_id', '=', 'users.id')
                      ->orderBy('users.nom', $order)
                      ->select('comptes.*');
                break;
            default:
                $query->orderBy('created_at', $order);
        }

        $limit = min($validated['limit'] ?? 10, 100);
        $comptes = $query->paginate($limit);

        $links = [
            'self' => $request->url() . '?' . http_build_query($validated),
            'first' => $request->url() . '?' . http_build_query(array_merge($validated, ['page' => 1])),
            'last' => $request->url() . '?' . http_build_query(array_merge($validated, ['page' => $comptes->lastPage()])),
        ];

        if ($comptes->hasMorePages()) {
            $links['next'] = $request->url() . '?' . http_build_query(array_merge($validated, ['page' => $comptes->currentPage() + 1]));
        }

        return $this->paginatedResponse(
            CompteResource::collection($comptes->items()),
            $comptes->currentPage(),
            $comptes->lastPage(),
            $comptes->total(),
            $comptes->perPage(),
            $links
        );
    }

    /**
     * @OA\Post(
     *     path="/comptes",
     *     summary="Créer un nouveau compte",
     *     description="Crée un nouveau compte bancaire pour un utilisateur",
     *     operationId="createCompte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "type"},
     *             @OA\Property(property="user_id", type="string", format="uuid", description="ID de l'utilisateur propriétaire"),
     *             @OA\Property(property="type", type="string", enum={"epargne", "cheque"}, description="Type de compte"),
     *             @OA\Property(property="solde", type="number", format="float", description="Solde initial", default=0),
     *             @OA\Property(property="devise", type="string", description="Devise", default="FCFA"),
     *             @OA\Property(property="statut", type="string", enum={"actif", "bloque", "ferme"}, description="Statut du compte", default="actif")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authentification requise",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Authentification requise")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur de validation"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(StoreCompteRequest $request): JsonResponse
    {
        $compte = Compte::create($request->validated());

        return $this->successResponse(
            new CompteResource($compte),
            'Compte créé avec succès',
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/comptes/{id}",
     *     summary="Détails d'un compte",
     *     description="Récupère les détails d'un compte spécifique",
     *     operationId="getCompte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du compte",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du compte récupérés",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authentification requise",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Authentification requise")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="details", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        $compte = Compte::with('user')->find($id);

        if (!$compte) {
            throw new CompteNotFoundException($id);
        }

        return $this->successResponse(new CompteResource($compte));
    }

    /**
     * @OA\Patch(
     *     path="/comptes/{id}",
     *     summary="Mettre à jour un compte",
     *     description="Met à jour partiellement un compte existant. Note: Les comptes bloqués peuvent être automatiquement archivés après expiration.",
     *     operationId="updateCompte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du compte",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="solde", type="number", format="float", description="Nouveau solde"),
     *             @OA\Property(property="statut", type="string", enum={"actif", "bloque", "ferme"}, description="Nouveau statut"),
     *             @OA\Property(property="dateFinBlocage", type="string", format="date-time", description="Date de fin de blocage (requis si statut=bloque)"),
     *             @OA\Property(property="metadonnees", type="object", description="Métadonnées additionnelles")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte mis à jour avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authentification requise",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Authentification requise")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation (ex: dateFinBlocage requise pour statut bloque)",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur de validation"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $this->validateRequest($request, [
            'solde' => 'sometimes|numeric|min:0',
            'statut' => 'sometimes|string|in:actif,bloque,ferme',
            'date_fin_blocage' => 'required_if:statut,bloque|nullable|date|after:now',
            'metadonnees' => 'sometimes|array',
        ]);

        $compte = Compte::find($id);

        if (!$compte) {
            throw new CompteNotFoundException($id);
        }

        $compte->update($validated);

        return $this->successResponse(
            new CompteResource($compte),
            'Compte mis à jour avec succès'
        );
    }

    /**
     * @OA\Delete(
     *     path="/comptes/{id}",
     *     summary="Archiver un compte",
     *     description="Archive un compte existant (soft delete). Les comptes bloqués expirés sont automatiquement archivés dans la base de données Neon.",
     *     operationId="archiveCompte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du compte à archiver",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte archivé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte archivé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authentification requise",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Authentification requise")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */

    public function getArchivedComptes(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Authentification requise', 401);
        }

        $validated = $this->validateRequest($request, [
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
            'statut' => 'nullable|string|in:actif,bloque,ferme',
            'raisonArchivage' => 'nullable|string|max:255',
        ]);

        // On veut aussi les comptes bloqués non archivés
        $query = Compte::query()->where(function($q) {
            $q->where('statut', 'bloque')
              ->orWhereNotNull('deleted_at');
        });

        if (isset($validated['statut']) && $validated['statut']) {
            $query->where('statut', $validated['statut']);
        }

        // Si vous avez une colonne ou un champ pour la raison d'archivage, ajoutez le filtre ici
        // if (isset($validated['raisonArchivage']) && $validated['raisonArchivage']) {
        //     $query->where('raison_archivage', 'like', "%{$validated['raisonArchivage']}%");
        // }

        $query->orderBy('updated_at', 'desc');

        $limit = min($validated['limit'] ?? 10, 100);
        $comptes = $query->paginate($limit);

        $links = [
            'self' => $request->url() . '?' . http_build_query($validated),
            'first' => $request->url() . '?' . http_build_query(array_merge($validated, ['page' => 1])),
            'last' => $request->url() . '?' . http_build_query(array_merge($validated, ['page' => $comptes->lastPage()])),
        ];
        if ($comptes->hasMorePages()) {
            $links['next'] = $request->url() . '?' . http_build_query(array_merge($validated, ['page' => $comptes->currentPage() + 1]));
        }

        return $this->paginatedResponse(
            CompteResource::collection($comptes->items()),
            $comptes->currentPage(),
            $comptes->lastPage(),
            $comptes->total(),
            $comptes->perPage(),
            $links
        );
    }

    /**
     * @OA\Post(
     *     path="/comptes/{id}/restaurer",
     *     summary="Restaurer un compte archivé",
     *     description="Restaure un compte depuis les archives Neon vers la base principale",
     *     operationId="restoreArchivedCompte",
     *     tags={"Archives"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du compte archivé à restaurer",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte restauré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte restauré avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authentification requise",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Authentification requise")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte archivé non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Compte archivé non trouvé")
     *         )
     *     )
     * )
     */
    public function restoreArchivedCompte(string $id): JsonResponse
    {
        $user = request()->user();

        // Vérifier les autorisations (seulement admin peut restaurer)
        if (!$user || $user->type !== 'admin') {
            return $this->errorResponse('Accès non autorisé', 403);
        }

        // Récupérer le compte archivé depuis Neon
        $compteArchive = \Illuminate\Support\Facades\DB::connection('neon')
            ->table('archived_comptes')
            ->where('id', $id)
            ->first();

        if (!$compteArchive) {
            return $this->errorResponse('Compte archivé non trouvé', 404);
        }

        try {
            // Créer un nouveau compte dans la base principale
            $nouveauCompte = Compte::create([
                'id' => $compteArchive->id,
                'numero_compte' => $compteArchive->numero_compte,
                'user_id' => $compteArchive->user_id,
                'type' => $compteArchive->type,
                'solde' => $compteArchive->solde,
                'devise' => $compteArchive->devise,
                'statut' => 'actif', // Remettre en actif lors de la restauration
                'metadonnees' => array_merge($compteArchive->metadonnees ?? [], [
                    'dateRestauration' => now()->toISOString(),
                    'raisonRestauration' => 'Restauration manuelle depuis les archives',
                    'restaurePar' => $user->id,
                ]),
            ]);

            // Supprimer de la base Neon
            \Illuminate\Support\Facades\DB::connection('neon')
                ->table('archived_comptes')
                ->where('id', $id)
                ->delete();

            return $this->successResponse(
                new CompteResource($nouveauCompte),
                'Compte restauré avec succès'
            );

        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la restauration du compte: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/comptes/{compteId}/bloquer",
     *     summary="Bloquer un compte",
     *     description="Bloque un compte épargne actif en spécifiant le motif et la durée de blocage",
     *     operationId="bloquerCompte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         required=true,
     *         description="ID du compte à bloquer",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"motif", "duree", "unite"},
     *             @OA\Property(property="motif", type="string", description="Motif du blocage"),
     *             @OA\Property(property="duree", type="integer", description="Durée du blocage"),
     *             @OA\Property(property="unite", type="string", enum={"mois"}, description="Unité de temps (mois uniquement)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte bloqué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte bloqué avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authentification requise",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Authentification requise")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation ou contraintes non respectées",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Impossible de bloquer ce compte")
     *         )
     *     )
     * )
     */
    public function bloquer(Request $request, string $compteId): JsonResponse
    {
        $validated = $this->validateRequest($request, [
            'motif' => 'required|string|max:255',
            'duree' => 'required|integer|min:1|max:12',
            'unite' => 'required|string|in:mois',
        ]);

        $compte = Compte::find($compteId);

        if (!$compte) {
            throw new CompteNotFoundException($compteId);
        }

        // Vérifications des contraintes
        if ($compte->type !== 'epargne') {
            return $this->errorResponse('Seuls les comptes épargne peuvent être bloqués', 400);
        }

        if ($compte->statut !== 'actif') {
            return $this->errorResponse('Seuls les comptes actifs peuvent être bloqués', 400);
        }

        // Calcul de la date de fin de blocage
        $dateFinBlocage = now()->addMonths($validated['duree']);

        // Mise à jour du compte
        $compte->update([
            'statut' => 'bloque',
            'date_fin_blocage' => $dateFinBlocage,
            'metadonnees' => array_merge($compte->metadonnees ?? [], [
                'motifBlocage' => $validated['motif'],
                'dateBlocage' => now()->toISOString(),
                'dureeBlocage' => $validated['duree'],
                'uniteBlocage' => $validated['unite'],
                'dateDeblocagePrevue' => $dateFinBlocage->toISOString(),
            ]),
        ]);

        return $this->successResponse(
            new CompteResource($compte),
            'Compte bloqué avec succès'
        );
    }

    /**
     * @OA\Post(
     *     path="/comptes/{compteId}/debloquer",
     *     summary="Débloquer un compte",
     *     description="Débloque un compte bloqué soit sur demande du client, soit à l'expiration de la période de blocage",
     *     operationId="debloquerCompte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         required=true,
     *         description="ID du compte à débloquer",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"motif"},
     *             @OA\Property(property="motif", type="string", description="Motif du déblocage")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte débloqué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte débloqué avec succès"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authentification requise",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Authentification requise")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation ou contraintes non respectées",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Impossible de débloquer ce compte")
     *         )
     *     )
     * )
     */
    public function debloquer(Request $request, string $compteId): JsonResponse
    {
        $validated = $this->validateRequest($request, [
            'motif' => 'required|string|max:255',
        ]);

        $compte = Compte::find($compteId);

        if (!$compte) {
            throw new CompteNotFoundException($compteId);
        }

        // Vérifications des contraintes
        if ($compte->statut !== 'bloque') {
            return $this->errorResponse('Seuls les comptes bloqués peuvent être débloqués', 400);
        }

        // Mise à jour du compte
        $metadonnees = $compte->metadonnees ?? [];
        $metadonnees['motifDeblocage'] = $validated['motif'];
        $metadonnees['dateDeblocage'] = now()->toISOString();

        $compte->update([
            'statut' => 'actif',
            'date_fin_blocage' => null,
            'metadonnees' => $metadonnees,
        ]);

        return $this->successResponse(
            new CompteResource($compte),
            'Compte débloqué avec succès'
        );
    }

    /**
     * Get list of blocked accounts
     */
    public function getBloquedComptes(Request $request): JsonResponse
    {
        $user = $request->user();

        // Vérifier les autorisations
        if (!$user) {
            return $this->errorResponse('Authentification requise', 401);
        }

        $validated = $this->validateRequest($request, [
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Compte::with('user')->nonSupprime()->where('statut', 'bloque');

        // Autorisation basée sur le rôle
        if ($user->type === 'client') {
            // Client ne voit que ses propres comptes
            $query->utilisateur($user->id);
        }

        // Pagination
        $limit = min($validated['limit'] ?? 10, 100);
        $comptes = $query->paginate($limit);

        $links = [
            'self' => $request->url() . '?' . http_build_query($validated),
            'first' => $request->url() . '?' . http_build_query(array_merge($validated, ['page' => 1])),
            'last' => $request->url() . '?' . http_build_query(array_merge($validated, ['page' => $comptes->lastPage()])),
        ];

        if ($comptes->hasMorePages()) {
            $links['next'] = $request->url() . '?' . http_build_query(array_merge($validated, ['page' => $comptes->currentPage() + 1]));
        }

        return $this->paginatedResponse(
            CompteResource::collection($comptes->items()),
            $comptes->currentPage(),
            $comptes->lastPage(),
            $comptes->total(),
            $comptes->perPage(),
            $links
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $compte = Compte::find($id);

        if (!$compte) {
            throw new CompteNotFoundException($id);
        }

        $compte->delete();

        return $this->successResponse(null, 'Compte archivé avec succès');
    }
}