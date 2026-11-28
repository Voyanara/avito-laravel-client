<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Persistence\Storage;

use Illuminate\Contracts\Encryption\DecryptException;
use Override;
use Voyanara\LaravelApiClient\Domain\Enums\GrantTypesEnum;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\AvitoClientsRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\TokenResponse;

readonly class DatabaseStorage implements TokenStorageInterface
{
    public function __construct(public AvitoClientsRepository $avitoClientsRepositories) {}

    #[Override]
    public function set(TokenResponse $tokenResponse, string $clientId, string $clientSecret): void
    {
        $this->avitoClientsRepositories->updateClient(
            md5($clientId),
            md5($clientSecret),
            $tokenResponse->accessToken,
            $tokenResponse->expiresIn,
            GrantTypesEnum::CLIENT_CREDENTIALS,
        );
    }

    #[Override]
    public function get(string $clientId, string $clientSecret): ?string
    {
        $client = $this->avitoClientsRepositories->getClientByCredentials($clientId, $clientSecret);

        try {
            return $client?->access_token;
        } catch (DecryptException) {
            // A plain text token of an older version or a rotated APP_KEY: a new token will be requested.
            return null;
        }
    }
}
