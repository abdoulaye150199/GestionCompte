<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Passport\HasApiTokens;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Compte;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable, \Laravel\Passport\HasApiTokens;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'password',
        'activation_code',
        'activation_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function client()
    {
        return $this->hasOne(Client::class);
    }

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }


    public static function createAccount(array $data, array $compteOverrides = []): self
    {
        // Try a normal transaction first. If connection is in an aborted state (Postgres 25P02)
        // we create a temporary DB connection and retry the same transactional work there.
        $defaultConn = DB::getDefaultConnection();
        try {
            DB::purge($defaultConn);
            DB::reconnect($defaultConn);
        } catch (\Throwable $e) {
            // ignore purge/reconnect failures
        }

        $work = function () use ($data, $compteOverrides) {
            // Try to find an existing user by email or telephone, but guard
            // against deployments where those columns may not exist.
            $existing = null;
            $hasEmail = \Illuminate\Support\Facades\Schema::hasColumn('users', 'email');
            $hasTelephone = \Illuminate\Support\Facades\Schema::hasColumn('users', 'telephone');

            if ($hasEmail || $hasTelephone) {
                $q = static::query();
                if ($hasEmail && ! empty($data['email'])) {
                    $q->where('email', $data['email']);
                }
                if ($hasTelephone && ! empty($data['telephone'])) {
                    $q->orWhere('telephone', $data['telephone']);
                }
                $existing = $q->first();
            }

            if ($existing && $existing->client) {
                // Ensure user_id is cast to string for UUID compatibility
                $existing->id = (string) $existing->id;
                $existing->client->user_id = (string) $existing->id;
                $existing->client->save();
                return $existing;
            }

            $passwordPlain = Str::random(10);
            $activationCode = random_int(100000, 999999);
            $activationExpires = now()->addMinutes(config('auth.activation_expires', 60));

            if (! $existing) {
                // If the users table has the expected columns (nom/prenom/email),
                // create the user via Eloquent. Some deployments use a legacy
                // schema (login/password) so we handle that case as a fallback.
                $hasNom = \Illuminate\Support\Facades\Schema::hasColumn('users', 'nom');

                if ($hasNom) {
                    // Cast values to strings where appropriate to avoid driver-specific
                    // binding/typing issues on some Postgres deployments.
                    $user = static::create([
                        'nom' => isset($data['nom']) ? (string) $data['nom'] : null,
                        'prenom' => isset($data['prenom']) ? (string) $data['prenom'] : null,
                        'email' => isset($data['email']) ? (string) $data['email'] : null,
                        'telephone' => isset($data['telephone']) ? (string) $data['telephone'] : null,
                        'password' => Hash::make($passwordPlain),
                        'activation_code' => (string) $activationCode,
                        'activation_expires_at' => $activationExpires,
                    ]);
                } else {
                    // Legacy/alternate schema: insert minimal record into users table
                    $insert = [
                        'password' => Hash::make($passwordPlain),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    // Prefer email as login if available, otherwise telephone
                    $login = null;
                    if (! empty($data['email'])) {
                        $login = (string) $data['email'];
                    } elseif (! empty($data['telephone'])) {
                        $login = (string) $data['telephone'];
                    } else {
                        $login = 'user_' . uniqid();
                    }

                    // Check for existing user by login before inserting
                    $existingLegacy = DB::table('users')->where('login', $login)->first();
                    if ($existingLegacy) {
                        return static::find($existingLegacy->id);
                    }

                    $insert['login'] = $login;

                    try {
                        $newId = DB::table('users')->insertGetId($insert);
                        $user = static::find($newId);
                    } catch (\Illuminate\Database\QueryException $ex) {
                        // Some deployments use a UUID primary key without a DB default.
                        // Retry by generating a UUID id and inserting the row explicitly.
                        $msg = $ex->getMessage();
                        if (str_contains($msg, 'null value in column "id"') || str_contains($msg, 'violates not-null constraint')) {
                            $insert['id'] = (string) \Illuminate\Support\Str::uuid();
                            DB::table('users')->insert($insert);
                            $user = static::where('id', $insert['id'])->first();
                        } else {
                            throw $ex;
                        }
                    }
                }
            } else {
                $user = $existing;
                // update activation code for existing user if needed
                $user->fill([
                    'activation_code' => (string) $activationCode,
                    'activation_expires_at' => $activationExpires,
                ])->save();
            }

            if (! $user->client) {
                $clientData = [
                    'id' => (string) Str::uuid(),
                    'user_id' => (string) $user->id,
                    'adresse' => $data['adresse'] ?? null,
                    'date_naissance' => $data['date_naissance'] ?? null,
                    'nci' => $data['nci'] ?? null,
                ];

                Client::create($clientData);
                $user->load('client');
            }

            // Create Compte for client
            $numero = Compte::generateNumero();
            $compteDefaults = [
                'id' => (string) Str::uuid(),
                'client_id' => (string) $user->client->id,
                'numero_compte' => $numero,
                'titulaire_compte' => $data['nom'] . ' ' . $data['prenom'],
                'user_id' => (string) $user->id,
                'type_compte' => 'courant',
                'solde' => 0,
                'devise' => 'FCFA',
                'statut_compte' => 'actif',
                'date_creation' => now(),
            ];
            $compteData = array_merge($compteDefaults, $compteOverrides);
            Compte::create($compteData);

            // Send email (simple example) — include activation code
            if (! empty($user->email)) {
                Mail::raw("Bienvenue {$user->prenom}, votre mot de passe est : {$passwordPlain}. Votre code d'activation: {$activationCode}", function ($message) use ($user) {
                    $message->to($user->email)->subject('Création de votre compte');
                });
            }

            // Send SMS via message service if available — currently we log the action in createAccount
            if (! empty($user->telephone)) {
                try {
                    $service = app()->make(\App\Services\MessageServiceInterface::class);
                    $service->sendMessage($user->telephone, "Votre code d'activation est {$activationCode}");
                } catch (\Throwable $e) {
                    Log::info("SMS fallback to log for {$user->telephone}: Votre code d'activation est {$activationCode}");
                }
            }

            return $user->fresh();
        };

        try {
            return $work();
        } catch (\Illuminate\Database\QueryException $ex) {
            // If Postgres reports the connection/transaction as aborted, retry on a fresh connection
            $code = $ex->getCode();
            $msg = $ex->getMessage();
            if ($code === '25P02' || str_contains($msg, 'current transaction is aborted')) {
                // create a temporary connection based on current default connection
                $connConfig = config("database.connections.{$defaultConn}");
                if (! $connConfig) {
                    throw $ex;
                }

                $tempName = $defaultConn . '_temp_' . uniqid();
                config(["database.connections.{$tempName}" => $connConfig]);
                // ensure fresh connection
                DB::purge($tempName);
                DB::reconnect($tempName);

                // If the new connection somehow starts in an aborted transaction state,
                // attempt an explicit ROLLBACK to clear it before starting our transaction.
                try {
                    $pdo = DB::connection($tempName)->getPdo();
                    // exec returns false on failure; wrap in try/catch to ignore errors
                    try {
                        $pdo->exec('ROLLBACK');
                    } catch (\Throwable $_) {
                        // ignore - ROLLBACK may fail if no transaction exists
                    }
                } catch (\Throwable $_) {
                    // if we cannot get PDO, proceed and let the transaction attempt fail
                }

                $prevDefault = config('database.default');
                DB::setDefaultConnection($tempName);
                try {
                    Log::info("Retrying createAccount on temporary DB connection {$tempName}");
                    return $work();
                } finally {
                    // restore default connection and disconnect temp
                    DB::setDefaultConnection($prevDefault);
                    try { DB::disconnect($tempName); } catch (\Throwable $_) {}
                }
            }

            throw $ex;
        }
    }
}
