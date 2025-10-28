<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Laravel\Passport\PersonalAccessClient;

class PassportServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Passport::useClientModel(Client::class);
        Passport::usePersonalAccessClientModel(PersonalAccessClient::class);

        Client::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });

        PersonalAccessClient::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}