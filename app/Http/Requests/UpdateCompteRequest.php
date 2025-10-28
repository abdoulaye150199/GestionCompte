<?php

namespace App\Http\Requests;

use App\Models\Compte;
use App\Traits\Validators\ValidationTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCompteRequest extends FormRequest
{
    use ValidationTrait;
    public function authorize()
    {
        return true;
    }

    protected function getClientId()
    {
        $ident = $this->route('identifiant');
        if (!$ident) {
            return null;
        }
        // Query the compte without eager-loading relations to avoid Eloquent building
        // a users.id IN (...) clause that may compare incompatible types (uuid vs int).
        $isUuid = (bool) preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $ident);
        $compteQuery = Compte::query();
        if ($isUuid) {
            $compteQuery->where('id', $ident);
        } else {
            $compteQuery->where('numero_compte', $ident);
        }

        $compte = $compteQuery->first(['user_id']);
        if (! $compte || empty($compte->user_id)) {
            return null;
        }

        // Safely fetch the user id using a text-cast comparison so Postgres won't
        // attempt to compare uuid = integer which raises an operator error.
        try {
            $userRow = \Illuminate\Support\Facades\DB::table('users')
                ->select('id')
                ->whereRaw("id::text = ?", [(string) $compte->user_id])
                ->first();
            return $userRow ? $userRow->id : null;
        } catch (\Throwable $e) {
            // On any DB error, return null so validation doesn't break; caller will
            // continue without a client id.
            return null;
        }
    }

    public function rules(): array
    {
        return [];
    }

    public function withValidator($validator)
    {

    }

    protected function passedValidation()
    {
        $clientId = $this->getClientId();
        $errors = $this->validateUpdateComptePayload($this->all(), $clientId);
        if (!empty($errors)) {
            throw new HttpResponseException(response()->json(['success' => false, 'errors' => $errors], 400));
        }
    }
}
