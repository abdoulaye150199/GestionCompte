<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admin extends Model
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

    // Méthodes spécifiques aux administrateurs
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

    public function getAdminPrivilegesAttribute(): array
    {
        return [
            'can_manage_users' => true,
            'can_manage_accounts' => true,
            'can_view_reports' => true,
            'can_manage_system' => true,
        ];
    }
}