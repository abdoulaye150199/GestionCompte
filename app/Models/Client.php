<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'nom',
        'nci',
        'email',
        'telephone',
        'adresse',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comptes(): HasMany
    {
        return $this->hasMany(Compte::class);
    }

    // Méthodes spécifiques aux clients
    public function getFullNameAttribute(): string
    {
        return $this->nom;
    }

    public function getContactInfoAttribute(): array
    {
        return [
            'email' => $this->email,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
        ];
    }
}