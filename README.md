# 🚀 API de Gestion des Clients & Comptes (Laravel + PostgreSQL + Docker + Render)

Une API RESTful moderne pour la gestion des clients et comptes bancaires, déployée sur Render avec Docker.

## Vue d'ensemble du projet

## 📋 Fonctionnalités

- ✅ Gestion des utilisateurs (Admin/Client)
- ✅ Gestion des comptes bancaires
- ✅ API RESTful niveau 3
- ✅ Authentification JWT (optionnel)
- ✅ Base de données PostgreSQL
- ✅ Déploiement Docker automatisé
- ✅ Documentation Swagger interactive

## 🛠️ Technologies

- **Laravel 10** - Framework PHP
- **PostgreSQL** - Base de données
- **Docker** - Conteneurisation
- **Render** - Déploiement cloud
- **Railway** - Base de données PostgreSQL

## 🚀 Déploiement sur Render

### Prérequis

1. **Repository GitHub** : Pousser le code sur GitHub
2. **Base de données Railway** : Déjà configurée
3. **Compte Render** : Créer un compte sur [render.com](https://render.com)

### Étapes de déploiement

#### 1. Créer un service Web sur Render

1. Aller sur [dashboard.render.com](https://dashboard.render.com)
2. Cliquer sur "New" → "Web Service"
3. Connecter votre repository GitHub
4. Configurer le service :
   - **Name** : `banque-api` (ou votre choix)
   - **Environment** : `Docker`
   - **Region** : `Frankfurt` (EU Central) ou région proche
   - **Branch** : `main` (ou votre branche principale)
   - **Root Directory** : `./` (racine du projet)

#### 2. Variables d'environnement

Dans les paramètres du service Render, ajouter ces variables :

```bash
# Base de données (Railway)
DB_CONNECTION=pgsql
DB_HOST=ballast.proxy.rlwy.net
DB_PORT=44054
DB_DATABASE=railway
DB_USERNAME=postgres
DB_PASSWORD=qaPPTWkqUEngIkSozVbfwWvgqNMrxWou

# Application
APP_NAME="API Banque"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://votre-app.onrender.com

# Cache & Sessions
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Logs
LOG_CHANNEL=stack
LOG_LEVEL=error
```

#### 3. Déploiement

Render détectera automatiquement le Dockerfile et commencera le build. Les migrations s'exécuteront automatiquement via le script `docker-entrypoint.sh`.

#### 4. URL finale

Votre API sera accessible via une URL comme : `https://banque-api.onrender.com`

## 🧪 Test local avec Docker

```bash
# Construire et lancer
docker-compose up --build

# L'application sera accessible sur http://localhost:8000
```

## 📚 API Endpoints

### Utilisateurs
- `GET /api/v1/users` - Lister tous les utilisateurs
- `GET /api/v1/users?type=client` - Filtrer par type
- `GET /api/v1/users/{id}` - Détails d'un utilisateur
- `POST /api/v1/users` - Créer un utilisateur
- `PATCH /api/v1/users/{id}` - Modifier un utilisateur
- `DELETE /api/v1/users/{id}` - Supprimer un utilisateur

### Comptes
- `GET /api/v1/comptes` - Lister tous les comptes
- `GET /api/v1/comptes/{id}` - Détails d'un compte
- `POST /api/v1/comptes` - Créer un compte
- `PATCH /api/v1/comptes/{id}` - Modifier un compte
- `DELETE /api/v1/comptes/{id}` - Supprimer un compte

## 📁 Structure du projet

```
├── app/
│   ├── Http/Controllers/Api/V1/
│   ├── Models/
│   └── Traits/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── docker/
│   ├── Dockerfile
│   ├── docker-compose.yml
│   └── docker-entrypoint.sh
└── README.md
```

## 🔒 Sécurité

- Variables d'environnement pour les secrets
- Migrations sécurisées
- Validation des données d'entrée
- Gestion des erreurs appropriée

## 📞 Support

Pour toute question ou problème :
1. Vérifier les logs Render
2. Tester localement avec Docker
3. Vérifier la connectivité Railway

---

**Déployé avec ❤️ sur Render**

## Architecture et principes appliqués

### Principes SOLID respectés

1. **Single Responsibility Principle (SRP)** : Chaque classe a une responsabilité unique
2. **Open/Closed Principle (OCP)** : Les classes sont extensibles sans modification
3. **Liskov Substitution Principle (LSP)** : Héritage correct des classes de base Laravel
4. **Interface Segregation Principle (ISP)** : Utilisation des conventions Laravel
5. **Dependency Inversion Principle (DIP)** : Injection de dépendances Laravel

## Méthodologie de développement

### Approche itérative et structurée

Le développement suit une approche méthodique, fichier par fichier, en respectant l'ordre logique des dépendances :

1. **Migration** : Définition de la structure de base de données
2. **Modèle** : Logique métier et relations
3. **Request** : Validation des données d'entrée
4. **Factory** : Génération de données de test
5. **Seeder** : Population de la base avec données cohérentes
6. **Mise à jour du DatabaseSeeder** : Intégration dans le processus de seeding

### Gestion des tâches avec TODO list

Chaque étape majeure utilise un système de TODO list pour tracker la progression :

```markdown
- [ ] Tâche à faire
- [x] Tâche terminée
```

## Étape 1 : Création du modèle User (anciennement Client)

### 1. Migration `create_clients_table.php`
**Commande** : `php artisan make:migration create_clients_table`

**Pourquoi cette migration ?**
- **Problème résolu** : Créer la structure de base de données pour stocker les utilisateurs avec leurs informations personnelles et d'authentification
- **Choix techniques** :
  - UUID comme clé primaire pour éviter les collisions et améliorer la sécurité
  - Contraintes d'unicité sur les champs sensibles (nci, email, telephone, login)
  - Champs nullable pour login/password car seuls les clients peuvent avoir ces champs
  - Index composite pour optimiser les recherches fréquentes

**Modifications apportées** :
- Renommage de la table en `users` (au lieu de `clients`) pour une meilleure sémantique
- Ajout des champs : `nom`, `nci`, `email`, `telephone`, `adresse`
- Ajout des champs d'authentification : `role`, `login`, `password`
- Configuration UUID comme clé primaire
- Index sur les champs fréquemment recherchés

**Code ajouté** :
```php
$table->uuid('id')->primary(); // UUID pour sécurité et scalabilité
$table->string('nom'); // Nom complet de l'utilisateur
$table->string('nci')->unique(); // Numéro CNI unique
$table->string('email')->unique(); // Email unique pour identification
$table->string('telephone')->unique(); // Téléphone unique
$table->text('adresse'); // Adresse complète
$table->enum('role', ['admin', 'client'])->default('client'); // Rôle utilisateur
$table->string('login')->nullable()->unique(); // Login optionnel pour clients
$table->string('password')->nullable(); // Mot de passe hashé optionnel
$table->index(['email', 'telephone', 'nom']); // Index pour recherches rapides
```

### 2. Modèle `User.php`
**Commande** : `php artisan make:model Client` puis renommé en `User.php`

**Pourquoi ce modèle ?**
- **Problème résolu** : Définir la logique métier des utilisateurs et leurs relations avec les comptes
- **Choix techniques** :
  - Configuration UUID pour cohérence avec la migration
  - Mass assignment sécurisé avec $fillable
  - Masquage du mot de passe pour la sécurité JSON
  - Relation bidirectionnelle avec les comptes

**Configuration UUID** :
```php
protected $keyType = 'string'; // Type de clé primaire string pour UUID
public $incrementing = false; // Désactive l'auto-incrémentation
```

**Champs fillable** :
```php
protected $fillable = [
    'id', 'nom', 'nci', 'email', 'telephone', 'adresse',
    'role', 'login', 'password'
]; // Définit les champs modifiables en masse pour la sécurité
```

**Sécurité** :
```php
protected $hidden = ['password']; // Masque le mot de passe dans les réponses JSON
```

**Relation hasMany** :
```php
public function comptes(): HasMany
{
    return $this->hasMany(Compte::class); // Un utilisateur peut avoir plusieurs comptes
}
```

### 3. Request `StoreUserRequest.php`
**Commande** : `php artisan make:request StoreClientRequest` puis renommé

**Pourquoi cette validation ?**
- **Problème résolu** : Valider les données d'entrée avant création d'un utilisateur pour garantir l'intégrité des données
- **Choix techniques** :
  - Validation côté serveur pour sécurité
  - Unicité des champs sensibles
  - Champs optionnels pour login/password (clients seulement)
  - Messages d'erreur automatiques en français

**Règles de validation** :
```php
return [
    'nom' => 'required|string|max:255', // Nom obligatoire, chaîne de caractères
    'nci' => 'required|string|unique:users,nci|max:255', // CNI unique obligatoire
    'email' => 'required|email|unique:users,email|max:255', // Email valide et unique
    'telephone' => 'required|string|unique:users,telephone|max:20', // Téléphone unique
    'adresse' => 'required|string|max:500', // Adresse obligatoire
    'role' => 'sometimes|in:admin,client', // Rôle optionnel mais limité aux valeurs valides
    'login' => 'nullable|string|unique:users,login|max:255', // Login optionnel pour clients
    'password' => 'nullable|string|min:8', // Mot de passe optionnel avec longueur minimale
];
```

### 4. Factory `UserFactory.php`
**Commande** : `php artisan make:factory ClientFactory` puis renommé

**Pourquoi cette factory ?**
- **Problème résolu** : Générer des données de test réalistes pour les utilisateurs sans créer des données en dur
- **Choix techniques** :
  - Utilisation de Faker pour des données variées et réalistes
  - Unicité garantie pour les champs sensibles
  - Hashage automatique du mot de passe pour la sécurité
  - Distribution équilibrée des rôles

**Génération de données** :
```php
return [
    'id' => $this->faker->uuid(), // UUID unique pour chaque utilisateur
    'nom' => $this->faker->name(), // Nom réaliste généré
    'nci' => $this->faker->unique()->numerify('##########'), // CNI unique à 10 chiffres
    'email' => $this->faker->unique()->safeEmail(), // Email unique et sécurisé
    'telephone' => $this->faker->unique()->phoneNumber(), // Numéro de téléphone unique
    'adresse' => $this->faker->address(), // Adresse complète réaliste
    'role' => $this->faker->randomElement(['admin', 'client']), // Rôle aléatoire
    'login' => $this->faker->unique()->userName(), // Login unique pour clients
    'password' => bcrypt('password'), // Mot de passe hashé pour sécurité
];
```

### 5. Seeder `UserSeeder.php`
**Commande** : `php artisan make:seeder ClientSeeder` puis renommé

**Pourquoi ce seeder ?**
- **Problème résolu** : Peupler la base de données avec des utilisateurs de test pour le développement
- **Choix techniques** :
  - Utilisation de la factory pour des données cohérentes
  - Nombre fixe d'utilisateurs pour tests prévisibles
  - Isolation du code pour faciliter les tests

**Population** :
```php
public function run(): void
{
    \App\Models\User::factory(10)->create(); // Crée 10 utilisateurs avec données variées
}
```

### 6. Mise à jour `DatabaseSeeder.php`
**Pourquoi cette modification ?**
- **Problème résolu** : Intégrer le UserSeeder dans le processus de seeding global de l'application
- **Choix techniques** :
  - Ordre logique : utilisateurs avant comptes
  - Utilisation de $this->call() pour une exécution propre
  - Maintien des autres seeders existants

**Ajout de l'appel au seeder** :
```php
$this->call(UserSeeder::class); // Exécute le UserSeeder en premier
```

## Étape 2 : Création du modèle Compte

### 1. Migration `create_comptes_table.php`
**Commande** : `php artisan make:migration create_comptes_table`

**Pourquoi cette migration ?**
- **Problème résolu** : Créer la structure de base de données pour les comptes bancaires avec toutes les propriétés requises
- **Choix techniques** :
  - UUID pour sécurité et scalabilité
  - Clé étrangère avec cascade delete pour intégrité référentielle
  - Types énumérés pour contrôler les valeurs possibles
  - JSON pour métadonnées extensibles
  - Index composites pour optimiser les requêtes fréquentes

**Structure créée** :
```php
$table->uuid('id')->primary(); // Clé primaire UUID
$table->string('numero_compte', 20)->unique(); // Numéro unique généré automatiquement
$table->foreignUuid('user_id')->constrained('users')->onDelete('cascade'); // FK vers users
$table->enum('type', ['epargne', 'cheque']); // Type de compte limité
$table->decimal('solde', 15, 2)->default(0); // Solde avec précision décimale
$table->string('devise', 10)->default('FCFA'); // Devise par défaut FCFA
$table->enum('statut', ['actif', 'bloque', 'ferme'])->default('actif'); // Statut par défaut actif
$table->json('metadonnees')->nullable(); // Métadonnées JSON flexibles
$table->index(['type', 'statut', 'numero_compte']); // Index pour recherches rapides
```

### 2. Modèle `Compte.php`
**Commande** : `php artisan make:model Compte`

**Pourquoi ce modèle ?**
- **Problème résolu** : Définir la logique métier des comptes bancaires et leur génération automatique de numéro
- **Choix techniques** :
  - Génération automatique du numéro de compte pour éviter les erreurs manuelles
  - Accesseurs pour transformer les données selon la structure JSON requise
  - Relations bidirectionnelles avec les utilisateurs
  - Vérification d'unicité pour éviter les collisions

**Configuration UUID** :
```php
protected $keyType = 'string'; // UUID comme clé primaire
public $incrementing = false; // Pas d'auto-incrémentation
```

**Génération automatique du numéro de compte** :
```php
protected static function boot()
{
    parent::boot();

    static::creating(function ($compte) {
        if (empty($compte->numero_compte)) {
            $compte->numero_compte = self::generateNumeroCompte(); // Génération automatique
        }
    });
}

private static function generateNumeroCompte(): string
{
    do {
        $numero = 'C' . strtoupper(Str::random(10)); // Format CXXXXXXXXXX
    } while (self::where('numero_compte', $numero)->exists()); // Vérification unicité

    return $numero;
}
```

**Accesseurs pour correspondre à la structure JSON** :
```php
public function getTitulaireAttribute(): string
{
    return $this->user->nom; // Retourne le nom du propriétaire du compte
}

public function getDateCreationAttribute()
{
    return $this->created_at; // Date de création du compte
}

public function getDerniereModificationAttribute()
{
    return $this->updated_at; // Dernière modification
}

public function getVersionAttribute(): int
{
    return 1; // Version statique pour l'instant
}
```

**Relation belongsTo** :
```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class); // Chaque compte appartient à un utilisateur
}
```

### 3. Request `StoreCompteRequest.php`
**Commande** : `php artisan make:request StoreCompteRequest`

**Pourquoi cette validation ?**
- **Problème résolu** : Valider les données d'entrée pour les comptes bancaires et garantir l'intégrité
- **Choix techniques** :
  - Vérification de l'existence de l'utilisateur propriétaire
  - Contrôle strict des valeurs énumérées
  - Validation du solde positif
  - Devise optionnelle avec taille contrôlée

**Validation** :
```php
return [
    'user_id' => 'required|exists:users,id', // Utilisateur doit exister
    'type' => 'required|in:epargne,cheque', // Type limité aux valeurs valides
    'solde' => 'numeric|min:0', // Solde numérique positif ou nul
    'devise' => 'string|size:3', // Devise à 3 caractères (optionnel)
    'statut' => 'in:actif,bloque,ferme', // Statut optionnel mais contrôlé
];
```

### 4. Factory `CompteFactory.php`
**Commande** : `php artisan make:factory CompteFactory`

**Pourquoi cette factory ?**
- **Problème résolu** : Générer des données de test réalistes pour les comptes bancaires
- **Choix techniques** :
  - Association automatique avec un utilisateur existant
  - Soldes réalistes et variés
  - Métadonnées JSON cohérentes avec la structure attendue
  - Distribution équilibrée des types et statuts

**Génération** :
```php
return [
    'id' => $this->faker->uuid(), // UUID unique pour le compte
    'user_id' => \App\Models\User::factory(), // Association avec un utilisateur
    'type' => $this->faker->randomElement(['epargne', 'cheque']), // Type aléatoire
    'solde' => $this->faker->randomFloat(2, 0, 1000000), // Solde réaliste
    'devise' => 'FCFA', // Devise par défaut
    'statut' => $this->faker->randomElement(['actif', 'bloque', 'ferme']), // Statut varié
    'metadonnees' => [
        'derniereModification' => now(), // Timestamp actuel
        'version' => 1, // Version initiale
    ],
];
```

### 5. Seeder `CompteSeeder.php`
**Commande** : `php artisan make:seeder CompteSeeder`

**Pourquoi ce seeder ?**
- **Problème résolu** : Créer des comptes bancaires réalistes associés aux utilisateurs existants
- **Choix techniques** :
  - Distribution aléatoire réaliste (1-3 comptes par utilisateur)
  - Association automatique avec tous les utilisateurs existants
  - Utilisation de la factory pour des données cohérentes

**Distribution réaliste** :
```php
public function run(): void
{
    \App\Models\User::all()->each(function ($user) {
        \App\Models\Compte::factory(rand(1, 3))->create([ // 1 à 3 comptes par utilisateur
            'user_id' => $user->id, // Association avec l'utilisateur
        ]);
    });
}
```

### 6. Mise à jour `DatabaseSeeder.php`
**Pourquoi cette modification ?**
- **Problème résolu** : Intégrer le CompteSeeder dans le processus global de seeding
- **Choix techniques** :
  - Ordre logique : utilisateurs avant comptes (dépendance)
  - Exécution séquentielle propre avec $this->call()
  - Maintien de l'ordre des opérations pour l'intégrité référentielle

**Ajout du seeder Compte** :
```php
$this->call(UserSeeder::class); // D'abord les utilisateurs
$this->call(CompteSeeder::class); // Ensuite les comptes associés
```

## Gestion des erreurs et corrections

### Problème de conflit de table `users`
**Erreur** : Table `users` déjà existante due à la migration par défaut de Laravel

**Solution** :
1. Suppression du fichier `2014_10_12_000000_create_users_table.php`
2. Modification de la migration personnalisée pour créer directement la table `users`
3. `php artisan migrate:fresh` pour recréer la base proprement

### Problème de longueur de champ `devise`
**Erreur** : Valeur trop longue pour le champ varchar(3)

**Solution** :
- Augmentation de la taille du champ `devise` de 3 à 10 caractères
- Augmentation de `numero_compte` à 20 caractères
- Re-migration complète

## Structure JSON respectée

### Ressource Compte
```json
{
  "id": "string",
  "numeroCompte": "string",
  "titulaire": "string",
  "type": "epargne | cheque",
  "solde": "number",
  "devise": "string",
  "dateCreation": "date",
  "statut": "actif | bloque | ferme",
  "metadonnees": {
    "derniereModification": "datetime",
    "version": "number"
  }
}
```

### Ressource Transaction
```json
{
  "id": "string",
  "compteId": "string",
  "type": "depot | retrait | virement | frais",
  "montant": "number",
  "devise": "string",
  "description": "string",
  "dateTransaction": "datetime",
  "statut": "en_attente | validee | annulee"
}
```

## Tests et validation

### Commandes de test exécutées
```bash
php artisan migrate:fresh  # Recréation complète de la base
php artisan db:seed        # Population avec données de test
```

### Résultats
- ✅ 10 utilisateurs créés avec rôles variés
- ✅ 15-30 comptes générés (1-3 par utilisateur)
- ✅ Numéros de compte uniques générés automatiquement
- ✅ Relations User ↔ Compte fonctionnelles
- ✅ Données cohérentes et réalistes

## Points forts de la méthodologie

1. **Approche systématique** : Un fichier à la fois, dans l'ordre logique
2. **Gestion des erreurs** : Identification et correction rapide des problèmes
3. **Respect des spécifications** : Adéquation parfaite avec la structure JSON demandée
4. **Qualité du code** : Application des principes SOLID et bonnes pratiques Laravel
5. **Tests continus** : Migration et seeding à chaque étape pour validation
6. **Documentation** : Code autodocumenté et commentaires explicites

## Étape 3 : Routes et Structure API

### Pourquoi cette étape ?
- **Problème résolu** : Créer une API RESTful complète avec versioning, gestion d'erreurs cohérente et format de réponse standardisé
- **Choix techniques** :
  - API Resource pour transformer les données selon la structure JSON demandée
  - Trait ApiResponse pour format de réponse uniforme
  - Exceptions personnalisées pour gestion d'erreurs spécifique
  - Controllers avec logique métier complète
  - Routes versionnées (v1) pour évolution future
  - CORS configuré pour accès externe

### 1. API Resources (`CompteResource.php` & `UserResource.php`)
**Pourquoi ces resources ?**
- **Problème résolu** : Transformer les données du modèle vers le format JSON exact demandé par la spécification
- **Choix techniques** :
  - Utilisation des accesseurs du modèle pour `titulaire`, `dateCreation`, etc.
  - Structure JSON respectant parfaitement la spécification
  - Séparation claire entre logique métier et présentation

**Transformation CompteResource** :
```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'numeroCompte' => $this->numero_compte,
        'titulaire' => $this->titulaire, // Accesseur du modèle
        'type' => $this->type,
        'solde' => $this->solde,
        'devise' => $this->devise,
        'dateCreation' => $this->date_creation, // Accesseur du modèle
        'statut' => $this->statut,
        'metadonnees' => [
            'derniereModification' => $this->derniere_modification, // Accesseur
            'version' => $this->version, // Accesseur
        ],
    ];
}
```

### 2. Trait ApiResponse (`ApiResponse.php`)
**Pourquoi ce trait ?**
- **Problème résolu** : Standardiser le format de toutes les réponses API pour cohérence
- **Choix techniques** :
  - Méthodes réutilisables dans tous les controllers
  - Format uniforme : `{success, message, data}`
  - Gestion spéciale de la pagination
  - Gestion d'erreurs intégrée

**Méthodes principales** :
```php
protected function successResponse($data = null, string $message = 'Success', int $statusCode = 200)
protected function errorResponse(string $message = 'Error', int $statusCode = 400, $errors = null)
protected function paginatedResponse($data, int $currentPage, int $totalPages, ...)
```

### 3. Exceptions personnalisées (`ApiException.php`, `CompteNotFoundException.php`, `UserNotFoundException.php`)
**Pourquoi ces exceptions ?**
- **Problème résolu** : Gestion d'erreurs spécifique avec messages et codes d'erreur appropriés
- **Choix techniques** :
  - Héritage de la classe de base ApiException
  - Format d'erreur JSON standardisé
  - Codes d'erreur spécifiques (COMPTE_NOT_FOUND, USER_NOT_FOUND)
  - Détails contextuels dans les erreurs

**Exemple CompteNotFoundException** :
```php
public function __construct(string $compteId)
{
    parent::__construct(
        'Le compte avec l\'ID spécifié n\'existe pas',
        404,
        [
            'code' => 'COMPTE_NOT_FOUND',
            'details' => ['compteId' => $compteId]
        ]
    );
}
```

### 4. Controllers (`CompteController.php` & `UserController.php`)
**Pourquoi ces controllers ?**
- **Problème résolu** : Implémenter la logique métier complète pour chaque endpoint RESTful
- **Choix techniques** :
  - Utilisation du trait ApiResponse pour réponses cohérentes
  - Gestion complète du CRUD avec validation
  - Filtres, tri et pagination avancés
  - Gestion d'erreurs avec exceptions personnalisées

**Fonctionnalités CompteController** :
- **index()** : Liste avec filtres (type, statut, search), tri (dateCreation, solde, titulaire), pagination
- **show()** : Détail d'un compte avec gestion d'erreur 404
- **store()** : Création avec validation via StoreCompteRequest
- **update()** : Mise à jour partielle des champs autorisés
- **destroy()** : Suppression avec cascade

**Exemple méthode index avec filtres avancés** :
```php
public function index(Request $request): JsonResponse
{
    $query = Compte::with('user');

    // Filtres multiples
    if ($request->type) $query->where('type', $request->type);
    if ($request->statut) $query->where('statut', $request->statut);
    if ($request->search) {
        $query->where('numero_compte', 'like', "%{$request->search}%")
              ->orWhereHas('user', fn($q) => $q->where('nom', 'like', "%{$request->search}%"));
    }

    // Tri flexible
    $sort = $request->get('sort', 'dateCreation');
    // Logique de tri...

    // Pagination avec métadonnées de liens
    $comptes = $query->paginate(min($request->limit ?? 10, 100));

    return $this->paginatedResponse(/* paramètres */);
}
```

### 5. Routes API (`routes/api.php`)
**Pourquoi cette configuration ?**
- **Problème résolu** : Organiser les routes par version pour évolution future et maintenabilité
- **Choix techniques** :
  - Préfixe `v1` pour versioning API
  - Routes resource pour CRUD complet automatique
  - Séparation claire des responsabilités

**Configuration** :
```php
Route::prefix('v1')->group(function () {
    Route::apiResource('comptes', CompteController::class);
    Route::apiResource('users', UserController::class);
});
```

### 6. Configuration CORS (`config/cors.php`)
**Pourquoi cette configuration ?**
- **Problème résolu** : Autoriser les requêtes cross-origin depuis les applications frontend
- **Choix techniques** :
  - Méthodes HTTP spécifiques autorisées
  - Cache des preflight requests (86400s = 24h)
  - Headers ouverts pour flexibilité

**Configuration appliquée** :
```php
'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
'max_age' => 86400, // Cache 24h
'allowed_origins' => ['*'], // À restreindre en production
```

## Endpoints API disponibles

### Comptes bancaires (`/api/v1/comptes`)

| Méthode | Endpoint | Description | Paramètres |
|---------|----------|-------------|------------|
| GET | `/api/v1/comptes` | Liste paginée avec filtres | `page`, `limit`, `type`, `statut`, `search`, `sort`, `order` |
| GET | `/api/v1/comptes/{id}` | Détail d'un compte | - |
| POST | `/api/v1/comptes` | Création | `user_id`, `type`, `solde`, `devise`, `statut` |
| PATCH | `/api/v1/comptes/{id}` | Mise à jour | `solde`, `statut`, `metadonnees` |
| DELETE | `/api/v1/comptes/{id}` | Suppression | - |

### Utilisateurs (`/api/v1/users`)

| Méthode | Endpoint | Description | Paramètres |
|---------|----------|-------------|------------|
| GET | `/api/v1/users` | Liste paginée avec filtres | `page`, `limit`, `role`, `search`, `sort`, `order` |
| GET | `/api/v1/users/{id}` | Détail d'un utilisateur | - |
| POST | `/api/v1/users` | Création | `nom`, `nci`, `email`, `telephone`, `adresse`, `role` |
| PATCH | `/api/v1/users/{id}` | Mise à jour | `nom`, `nci`, `email`, `telephone`, `adresse`, `role` |
| DELETE | `/api/v1/users/{id}` | Suppression | - |

## Format de réponse standardisé

### Succès
```json
{
  "success": true,
  "message": "Opération réussie",
  "data": { /* données */ }
}
```

### Erreur
```json
{
  "success": false,
  "message": "Description de l'erreur",
  "errors": {
    "code": "ERROR_CODE",
    "details": { /* contexte */ }
  }
}
```

### Pagination
```json
{
  "success": true,
  "data": [ /* items */ ],
  "pagination": {
    "currentPage": 1,
    "totalPages": 5,
    "totalItems": 50,
    "itemsPerPage": 10,
    "hasNext": true,
    "hasPrevious": false
  },
  "links": {
    "self": "/api/v1/comptes?page=1",
    "next": "/api/v1/comptes?page=2",
    "first": "/api/v1/comptes?page=1",
    "last": "/api/v1/comptes?page=5"
  }
}
```

## Tests et validation

### Commandes de test exécutées
```bash
php artisan route:list  # Vérification des routes
php artisan config:cache  # Cache de configuration
```

### Résultats
- ✅ Routes correctement enregistrées avec préfixe v1
- ✅ Controllers accessibles via les bonnes URL
- ✅ CORS configuré pour les requêtes externes
- ✅ Format de réponse cohérent dans toute l'API
- ✅ Gestion d'erreurs avec messages personnalisés

Cette architecture API respecte parfaitement les principes REST, fournit une expérience développeur optimale et facilite l'évolution future grâce au versioning.

## Installation et Configuration de Swagger

### 1. Installation du package Laravel Swagger

```bash
composer require "darkaonline/l5-swagger"
```

**Résultat attendu :**
```
./composer.json has been updated
Running composer update darkaonline/l5-swagger
Loading composer repositories with package information
Updating dependencies
Lock file operations: 5 installs, 0 updates, 0 removals
...
Package doctrine/annotations is abandoned, you should avoid using it. No replacement was suggested.
...
Using version ^8.6 for darkaonline/l5-swagger
```

### 2. Publication des fichiers de configuration

```bash
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

**Résultat attendu :**
```
INFO  Publishing assets.

Copying file [vendor/darkaonline/l5-swagger/config/l5-swagger.php] to [config/l5-swagger.php]  DONE
Copying directory [vendor/darkaonline/l5-swagger/resources/views] to [resources/views/vendor/l5-swagger]  DONE
```

### 3. Ajout des annotations Swagger aux contrôleurs

#### Annotations dans `CompteController.php` :

```php
/**
 * @OA\Info(
 *     title="API de Gestion des Clients & Comptes",
 *     version="1.0.0",
 *     description="API RESTful pour la gestion des clients et de leurs comptes bancaires"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="Serveur de développement"
 * )
 *
 * @OA\Schema(
 *     schema="ApiResponse",
 *     @OA\Property(property="success", type="boolean"),
 *     @OA\Property(property="message", type="string"),
 *     @OA\Property(property="data", type="object")
 * )
 *
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     @OA\Property(property="currentPage", type="integer"),
 *     @OA\Property(property="totalPages", type="integer"),
 *     @OA\Property(property="totalItems", type="integer"),
 *     @OA\Property(property="itemsPerPage", type="integer"),
 *     @OA\Property(property="hasNext", type="boolean"),
 *     @OA\Property(property="hasPrevious", type="boolean")
 * )
 *
 * @OA\Schema(
 *     schema="Compte",
 *     @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="numeroCompte", type="string", example="C00123456"),
 *     @OA\Property(property="titulaire", type="string", example="Amadou Diallo"),
 *     @OA\Property(property="type", type="string", enum={"epargne", "cheque"}),
 *     @OA\Property(property="solde", type="number", format="float", example=1250000),
 *     @OA\Property(property="devise", type="string", example="FCFA"),
 *     @OA\Property(property="dateCreation", type="string", format="date-time"),
 *     @OA\Property(property="statut", type="string", enum={"actif", "bloque", "ferme"}),
 *     @OA\Property(property="metadonnees", type="object",
 *         @OA\Property(property="derniereModification", type="string", format="date-time"),
 *         @OA\Property(property="version", type="integer", example=1)
 *     )
 * )
 */
class CompteController extends Controller
{
    // ... méthodes avec annotations @OA\Get, @OA\Post, etc.
}
```

#### Exemple d'annotation pour un endpoint :

```php
/**
 * @OA\Get(
 *     path="/comptes",
 *     summary="Lister tous les comptes",
 *     description="Récupère une liste paginée de comptes avec possibilité de filtrage et tri",
 *     operationId="getComptes",
 *     tags={"Comptes"},
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Numéro de page",
 *         required=false,
 *         @OA\Schema(type="integer", default=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des comptes récupérée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Compte")),
 *             @OA\Property(property="pagination", ref="#/components/schemas/PaginationMeta")
 *         )
 *     )
 * )
 */
public function index(Request $request): JsonResponse
{
    // ... logique de la méthode
}
```

### 4. Génération de la documentation Swagger

```bash
php artisan l5-swagger:generate
```

**Résultat attendu :**
```
Regenerating docs default
```

### 5. Vérification des routes API

```bash
php artisan route:list --path=api
```

**Résultat attendu :**
```
GET|HEAD        api/documentation l5-swagger.default.api › L5Swagger\Http\Controllers\SwaggerController@api
GET|HEAD        api/oauth2-callback l5-swagger.default.oauth2_callback › L5Swagger\Http\Controllers\SwaggerController@oauth2Callback
GET|HEAD        api/user ...................................................
GET|HEAD        api/v1/comptes comptes.index › Api\V1\CompteController@index
POST            api/v1/comptes comptes.store › Api\V1\CompteController@store
GET|HEAD        api/v1/comptes/{compte} comptes.show › Api\V1\CompteController@show
PUT|PATCH       api/v1/comptes/{compte} comptes.update › Api\V1\CompteController@update
DELETE          api/v1/comptes/{compte} comptes.destroy › Api\V1\CompteController@destroy
GET|HEAD        api/v1/users ..... users.index › Api\V1\UserController@index
POST            api/v1/users ..... users.store › Api\V1\UserController@store
GET|HEAD        api/v1/users/{user} users.show › Api\V1\UserController@show
PUT|PATCH       api/v1/users/{user} users.update › Api\V1\UserController@update
DELETE          api/v1/users/{user} users.destroy › Api\V1\UserController@destroy
```

### 6. Accès à la documentation

**URL de la documentation interactive :**
```
http://localhost:8000/api/documentation
```

### 7. Configuration du fichier `config/l5-swagger.php` (optionnel)

Si vous voulez personnaliser la configuration :

```php
return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'API de Gestion des Clients & Comptes',
            ],
            'routes' => [
                'api' => 'api/documentation',
            ],
            'paths' => [
                'docs' => storage_path('api-docs'),
                'docs_json' => 'api-docs.json',
                'docs_yaml' => 'api-docs.yaml',
                'format_to_use_for_docs' => env('L5_SWAGGER_FORMAT', 'json'),
                'annotations' => [
                    base_path('app'),
                ],
            ],
        ],
    ],
];
```

## Tests de l'API avec Swagger

1. **Démarrer le serveur Laravel :**
```bash
php artisan serve
```

2. **Accéder à Swagger UI :**
```
http://localhost:8000/api/documentation
```

3. **Tester les endpoints :**
   - Cliquer sur un endpoint pour l'étendre
   - Cliquer sur "Try it out"
   - Remplir les paramètres si nécessaire
   - Cliquer sur "Execute"
   - Voir la réponse dans la section "Responses"

## Exemple de test : Créer un compte

1. Dans Swagger UI, aller à `POST /api/v1/comptes`
2. Cliquer sur "Try it out"
3. Dans le corps de la requête, entrer :
```json
{
  "user_id": "uuid-d-un-utilisateur-existant",
  "type": "epargne",
  "solde": 100000,
  "devise": "FCFA",
  "statut": "actif"
}
```
4. Cliquer sur "Execute"
5. Voir la réponse avec le compte créé

Cette méthodologie assure un développement robuste, maintenable et conforme aux exigences du projet.

## 🚀 Guide de Test Complet de l'API

### Prérequis pour les tests

#### 1. Installation et configuration de l'environnement

```bash
# 1. Cloner le projet
git clone <repository-url>
cd bankProjet

# 2. Installer les dépendances
composer install

# 3. Copier le fichier d'environnement
cp .env.example .env

# 4. Configurer la base de données dans .env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=bank_api_test
DB_USERNAME=postgres
DB_PASSWORD=your_password

# 5. Générer la clé d'application
php artisan key:generate

# 6. Installer Passport pour l'authentification
php artisan passport:install

# 7. Créer et migrer la base de données
php artisan migrate:fresh

# 8. Peupler avec des données de test
php artisan db:seed

# 9. Démarrer le serveur
php artisan serve
```

#### 2. Données de test créées automatiquement

Après `php artisan db:seed`, vous aurez :

- **10 utilisateurs** (5 admins, 5 clients)
- **15-30 comptes** bancaires (1-3 comptes par utilisateur)
- **Numéros de compte** uniques générés automatiquement (format: CXXXXXXXXXX)

### 📋 Tests étape par étape

#### Étape 1 : Vérification de l'installation

```bash
# Vérifier que les migrations sont appliquées
php artisan migrate:status

# Vérifier les routes API
php artisan route:list --path=api

# Vérifier que Swagger fonctionne
php artisan l5-swagger:generate
```

#### Étape 2 : Test de l'authentification Passport

##### 2.1 Créer un client OAuth (si pas déjà fait)

```bash
php artisan passport:client --personal
# Nom : "API Client"
```

##### 2.2 Obtenir un token d'authentification

**Via Tinker :**
```bash
php artisan tinker
```

```php
// Pour un admin
$user = \App\Models\User::where('role', 'admin')->first();
$token = $user->createToken('API Token')->accessToken;
echo "Admin Token: " . $token;

// Pour un client
$user = \App\Models\User::where('role', 'client')->first();
$token = $user->createToken('API Token')->accessToken;
echo "Client Token: " . $token;
```

**Via API (méthode alternative) :**
```bash
# Créer un utilisateur test
curl -X POST "http://localhost:8000/api/v1/register" \
  -H "Content-Type: application/json" \
  -d '{
    "login": "testuser",
    "password": "password123",
    "nom": "Test User",
    "nci": "TEST123456",
    "email": "test@example.com",
    "telephone": "+221771234567",
    "adresse": "Dakar, Sénégal"
  }'

# Se connecter pour obtenir un token
curl -X POST "http://localhost:8000/api/v1/login" \
  -H "Content-Type: application/json" \
  -d '{
    "login": "testuser",
    "password": "password123"
  }'
```

#### Étape 3 : Tests des endpoints Comptes

##### 3.1 Test de l'accès non autorisé

```bash
# Devrait retourner 401 Unauthorized
curl -X GET "http://localhost:8000/api/v1/comptes" \
  -H "Accept: application/json"
```

**Réponse attendue :**
```json
{
  "message": "Unauthenticated."
}
```

##### 3.2 Test de la liste des comptes (Admin)

```bash
curl -X GET "http://localhost:8000/api/v1/comptes" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {ADMIN_TOKEN}"
```

**Réponse attendue :**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid-compte-1",
      "numeroCompte": "C123456789",
      "titulaire": "Amadou Diallo",
      "type": "epargne",
      "solde": 1250000,
      "devise": "FCFA",
      "dateCreation": "2025-10-25T12:00:00Z",
      "statut": "actif",
      "metadonnees": {
        "derniereModification": "2025-10-25T12:00:00Z",
        "version": 1
      }
    }
  ],
  "pagination": {
    "currentPage": 1,
    "totalPages": 2,
    "totalItems": 15,
    "itemsPerPage": 10,
    "hasNext": true,
    "hasPrevious": false
  },
  "links": {
    "self": "/api/v1/comptes?page=1&limit=10",
    "next": "/api/v1/comptes?page=2&limit=10",
    "first": "/api/v1/comptes?page=1&limit=10",
    "last": "/api/v1/comptes?page=2&limit=10"
  }
}
```

##### 3.3 Test de la liste des comptes (Client)

```bash
curl -X GET "http://localhost:8000/api/v1/comptes" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {CLIENT_TOKEN}"
```

**Note :** Un client ne voit que ses propres comptes.

##### 3.4 Test des filtres

```bash
# Filtrer par type
curl -X GET "http://localhost:8000/api/v1/comptes?type=epargne" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {ADMIN_TOKEN}"

# Filtrer par statut
curl -X GET "http://localhost:8000/api/v1/comptes?statut=actif" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {ADMIN_TOKEN}"

# Recherche par numéro ou nom
curl -X GET "http://localhost:8000/api/v1/comptes?search=C123456789" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {ADMIN_TOKEN}"
```

##### 3.5 Test du tri

```bash
# Tri par solde décroissant
curl -X GET "http://localhost:8000/api/v1/comptes?sort=solde&order=desc" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {ADMIN_TOKEN}"

# Tri par date de création
curl -X GET "http://localhost:8000/api/v1/comptes?sort=dateCreation&order=asc" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {ADMIN_TOKEN}"
```

##### 3.6 Test de la pagination

```bash
# Page 1 avec 5 éléments par page
curl -X GET "http://localhost:8000/api/v1/comptes?page=1&limit=5" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {ADMIN_TOKEN}"

# Page 2
curl -X GET "http://localhost:8000/api/v1/comptes?page=2&limit=5" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {ADMIN_TOKEN}"
```

##### 3.7 Test de récupération d'un compte spécifique

```bash
curl -X GET "http://localhost:8000/api/v1/comptes/{UUID_COMPTE}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {ADMIN_TOKEN}"
```

**Test d'erreur (compte inexistant) :**
```bash
curl -X GET "http://localhost:8000/api/v1/comptes/uuid-inexistant" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {ADMIN_TOKEN}"
```

**Réponse d'erreur attendue :**
```json
{
  "success": false,
  "message": "Le compte avec l'ID spécifié n'existe pas",
  "errors": {
    "code": "COMPTE_NOT_FOUND",
    "details": {
      "compteId": "uuid-inexistant"
    }
  }
}
```

##### 3.8 Test de création d'un compte

```bash
curl -X POST "http://localhost:8000/api/v1/comptes" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {ADMIN_TOKEN}" \
  -d '{
    "user_id": "uuid-d-un-utilisateur-existant",
    "type": "epargne",
    "solde": 1000000,
    "devise": "FCFA",
    "statut": "actif"
  }'
```

**Réponse attendue :**
```json
{
  "success": true,
  "message": "Compte créé avec succès",
  "data": {
    "id": "nouveau-uuid",
    "numeroCompte": "C987654321",
    "titulaire": "Nom de l'utilisateur",
    "type": "epargne",
    "solde": 1000000,
    "devise": "FCFA",
    "dateCreation": "2025-10-25T12:30:00Z",
    "statut": "actif",
    "metadonnees": {
      "derniereModification": "2025-10-25T12:30:00Z",
      "version": 1
    }
  }
}
```

##### 3.9 Test de mise à jour d'un compte

```bash
curl -X PATCH "http://localhost:8000/api/v1/comptes/{UUID_COMPTE}" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {ADMIN_TOKEN}" \
  -d '{
    "solde": 1500000,
    "statut": "bloque"
  }'
```

##### 3.10 Test de suppression d'un compte

```bash
curl -X DELETE "http://localhost:8000/api/v1/comptes/{UUID_COMPTE}" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {ADMIN_TOKEN}"
```

#### Étape 4 : Tests des endpoints Utilisateurs

##### 4.1 Liste des utilisateurs

```bash
curl -X GET "http://localhost:8000/api/v1/users" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {ADMIN_TOKEN}"
```

##### 4.2 Création d'un utilisateur

```bash
curl -X POST "http://localhost:8000/api/v1/users" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {ADMIN_TOKEN}" \
  -d '{
    "nom": "Nouveau Client",
    "nci": "NEW123456",
    "email": "nouveau@example.com",
    "telephone": "+221771234568",
    "adresse": "Saint-Louis, Sénégal",
    "role": "client"
  }'
```

#### Étape 5 : Tests avec Swagger UI

1. **Accéder à la documentation :**
   ```
   http://localhost:8000/api/documentation
   ```

2. **Tester les endpoints :**
   - Sélectionner un endpoint
   - Cliquer sur "Try it out"
   - Remplir les paramètres
   - Ajouter le token dans "Authorize" : `Bearer {TOKEN}`
   - Cliquer sur "Execute"

#### Étape 6 : Tests de performance et limites

##### 6.1 Test de limite de débit (Rate Limiting)

```bash
# Faire plusieurs requêtes rapides pour tester le rate limiting
for i in {1..20}; do
  curl -X GET "http://localhost:8000/api/v1/comptes" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer {TOKEN}" \
    -w "%{http_code}\n" -o /dev/null -s
done
```

##### 6.2 Test de charge avec pagination

```bash
# Tester avec différentes tailles de page
curl -X GET "http://localhost:8000/api/v1/comptes?limit=100" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {ADMIN_TOKEN}"
```

#### Étape 7 : Tests d'erreurs et edge cases

##### 7.1 Test de validation

```bash
# Test avec données invalides
curl -X POST "http://localhost:8000/api/v1/comptes" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {ADMIN_TOKEN}" \
  -d '{
    "user_id": "invalid-uuid",
    "type": "invalid_type",
    "solde": -1000
  }'
```

##### 7.2 Test d'autorisation

```bash
# Client essayant d'accéder aux comptes d'un autre client
curl -X GET "http://localhost:8000/api/v1/comptes" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {CLIENT_TOKEN}"
# Devrait seulement retourner les comptes du client connecté
```

#### Étape 8 : Tests automatisés (optionnel)

Créer des tests PHPUnit :

```bash
# Créer un test pour les comptes
php artisan make:test CompteApiTest

# Exécuter les tests
php artisan test
```

### 📊 Résumé des tests à effectuer

| Test | Endpoint | Méthode | Auth | Description |
|------|----------|---------|------|-------------|
| ✅ | `/api/v1/comptes` | GET | Admin | Liste tous les comptes |
| ✅ | `/api/v1/comptes` | GET | Client | Liste ses comptes uniquement |
| ✅ | `/api/v1/comptes?type=epargne` | GET | Admin | Filtre par type |
| ✅ | `/api/v1/comptes?statut=actif` | GET | Admin | Filtre par statut |
| ✅ | `/api/v1/comptes?search=XXX` | GET | Admin | Recherche |
| ✅ | `/api/v1/comptes?page=1&limit=10` | GET | Admin | Pagination |
| ✅ | `/api/v1/comptes/{id}` | GET | Admin | Détail compte |
| ✅ | `/api/v1/comptes` | POST | Admin | Création compte |
| ✅ | `/api/v1/comptes/{id}` | PATCH | Admin | Mise à jour compte |
| ✅ | `/api/v1/comptes/{id}` | DELETE | Admin | Suppression compte |
| ✅ | `/api/v1/login` | POST | - | Authentification |
| ✅ | `/api/v1/register` | POST | - | Inscription |
| ✅ | `/api/v1/logout` | POST | Token | Déconnexion |

### 🎯 Checklist de validation finale

- [ ] Installation complète sans erreur
- [ ] Migrations appliquées correctement
- [ ] Données de test générées
- [ ] Authentification fonctionnelle
- [ ] Endpoints CRUD opérationnels
- [ ] Filtres et tri fonctionnels
- [ ] Pagination correcte
- [ ] Gestion d'erreurs appropriée
- [ ] Autorisation respectée (Admin vs Client)
- [ ] Documentation Swagger accessible
- [ ] Tests manuels passés
- [ ] Performance acceptable

Cette méthodologie de test complète assure que l'API est robuste, sécurisée et prête pour la production ! 🚀
