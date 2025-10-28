<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $fillable = [
        'id',
        'login',
        'password',
        'type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class);
    }

    public function comptes(): HasOne
    {
        return $this->hasOne(Compte::class, 'user_id');
    }

    public function getTypeAttribute(): string
    {
        if ($this->client) {
            return 'client';
        } elseif ($this->admin) {
            return 'admin';
        }
        return 'unknown';
    }

    public function getProfileAttribute()
    {
        return $this->client ?? $this->admin;
    }

    /**
     * Scope pour filtrer par type d'utilisateur
     */
    public function scopeOfType($query, $type)
    {
        $table = $type . 's';
        return $query->whereHas($table, function ($q) use ($table) {
            $q->whereNotNull('id');
        });
    }

    /**
     * Scope pour les clients uniquement
     */
    public function scopeClients($query)
    {
        return $query->ofType('client');
    }

    /**
     * Scope pour les admins uniquement
     */
    public function scopeAdmins($query)
    {
        return $query->ofType('admin');
    }
}
