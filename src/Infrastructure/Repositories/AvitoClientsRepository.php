<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Repositories;

use Voyanara\LaravelApiClient\Domain\Enums\GrantTypesEnum;
use Voyanara\LaravelApiClient\Infrastructure\Models\AvitoClientModel;

class AvitoClientsRepository
{
    public function getClient(): ?AvitoClientModel
    {
        return AvitoClientModel::first();
    }

    public function getClientByCredentials(string $clientId, string $clientSecret): ?AvitoClientModel
    {
        return AvitoClientModel::where('client_id', '=', md5($clientId))
            ->where('client_secret', '=', md5($clientSecret))
            ->first();
    }

    public function updateClient(string $clientId, string $secret, string $token, int $expires, GrantTypesEnum $grand): AvitoClientModel
    {

        return AvitoClientModel::updateOrCreate(
            [
                'client_id' => $clientId,
                'client_secret' => $secret,
                'grant_type' => $grand->value,
            ],
            [
                'access_token' => $token,
                'expires_in' => $expires,
            ]
        );
    }
}
