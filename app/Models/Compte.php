<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Compte extends Model
{
    use HasFactory;

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
    ];

    protected $casts = [
        'solde' => 'decimal:2',
        'metadonnees' => 'array',
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
}
