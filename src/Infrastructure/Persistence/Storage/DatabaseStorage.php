<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Persistence\Storage;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Override;
use Voyanara\LaravelApiClient\Domain\Enums\GrantTypesEnum;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\AvitoClientsRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\TokenResponse;

/**
 * Keeps tokens in the avito_module_clients table, encrypted with the application key (APP_KEY).
 */
readonly class DatabaseStorage implements TokenStorageInterface
{
    public function __construct(public AvitoClientsRepository $avitoClientsRepositories) {}

    #[Override]
    public function set(TokenResponse $tokenResponse, string $clientId, string $clientSecret): void
    {
        $this->avitoClientsRepositories->updateClient(
            md5($clientId),
            md5($clientSecret),
            Crypt::encryptString($tokenResponse->accessToken),
            $tokenResponse->expiresIn,
            GrantTypesEnum::CLIENT_CREDENTIALS,
        );
    }

    #[Override]
    public function get(string $clientId, string $clientSecret): ?string
    {
        $encrypted = $this->avitoClientsRepositories->getClientByCredentials($clientId, $clientSecret)?->access_token;

        if (! is_string($encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (DecryptException) {
            // A plain text token of an older version or a rotated APP_KEY: a new token will be requested.
            return null;
        }
    }
}
