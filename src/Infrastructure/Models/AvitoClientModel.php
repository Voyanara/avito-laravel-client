<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AvitoClientModel extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    protected $table = 'avito_module_clients';

    protected $fillable = [
        'client_id',
        'client_secret',
        'grant_type',
        'expires_in',
        'access_token',
        'created_at',
        'updated_at',
    ];
}
