<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Persistence\Storage;

use Voyanara\LaravelApiClient\Domain\Enums\GrantTypesEnum;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\AvitoClientsRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\TokenResponse;
use Override;

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

        return $client?->access_token;
    }
}
