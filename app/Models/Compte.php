<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Compte extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'numero_compte',
        'user_id',
        'type',
        'solde',
        'devise',
        'statut',
        'metadonnees',
        'date_debut_blocage',
        'date_fin_blocage',
    ];

    protected $casts = [
        'solde' => 'decimal:2',
        'metadonnees' => 'array',
        'date_debut_blocage' => 'datetime',
        'date_fin_blocage' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($compte) {
            if (empty($compte->numero_compte)) {
                $compte->numero_compte = self::generateNumeroCompte();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    private static function generateNumeroCompte(): string
    {
        do {
            $numero = 'C' . strtoupper(Str::random(10));
        } while (self::where('numero_compte', $numero)->exists());

        return $numero;
    }

    public function getTitulaireAttribute(): string
    {
        return $this->user->nom ?? 'Utilisateur inconnu';
    }

    public function getDateCreationAttribute()
    {
        return $this->created_at;
    }

    public function getDerniereModificationAttribute()
    {
        return $this->updated_at;
    }

    public function getVersionAttribute(): int
    {
        return 1; // Pour l'instant, version statique
    }

    /**
     * Scope pour exclure les comptes supprimés (soft delete)
     */
    public function scopeNonSupprime($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope pour rechercher par numéro de compte
     */
    public function scopeNumero($query, $numero)
    {
        return $query->where('numero_compte', 'like', "%{$numero}%");
    }

    /**
     * Scope pour filtrer les comptes d'un client par téléphone
     */
    public function scopeClient($query, $telephone)
    {
        return $query->whereHas('user', function ($userQuery) use ($telephone) {
            $userQuery->where('telephone', $telephone);
        });
    }

    /**
     * Scope pour filtrer par type
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour filtrer par statut
     */
    public function scopeStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope pour filtrer les comptes d'un utilisateur spécifique
     */
    public function scopeUtilisateur($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
