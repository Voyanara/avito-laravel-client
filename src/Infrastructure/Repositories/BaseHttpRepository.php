<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Repositories;

use BackedEnum;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientCredentialsException;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Infrastructure\Service\RequestService;
use Voyanara\LaravelApiClient\Presentation\Responses\TokenResponse;

abstract class BaseHttpRepository
{
    /**
     * @param  string|null  $token  Not used: the access token is resolved lazily by RequestService.
     *
     * @throws ClientCredentialsException
     */
    public function __construct(
        public string $apiUrl,
        public readonly ?string $clientId,
        public readonly ?string $secret,
        public readonly TokenStorageInterface $tokenStorage,
        public readonly RequestService $requestService,
        /** @deprecated Not used since 1.2.1, always null: RequestService manages the token. Will be removed in 2.0. */
        public ?string $token = null,
    ) {
        $this->init();
    }

    /**
     * Passes the credentials to RequestService. No HTTP request is made here:
     * the access token is fetched on the first API call and refreshed when it expires.
     *
     * @throws ClientCredentialsException
     */
    protected function init(): void
    {
        if (! $this->clientId || ! $this->secret) {
            throw new ClientCredentialsException('Client id and secret must be set');
        }

        $this->requestService->setTokenCredentials(
            $this->tokenStorage,
            $this->clientId,
            $this->secret,
            $this->apiUrl
        );
    }

    /**
     * Requests a new client_credentials token. Does not store it.
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getToken(string $clientId, string $secret): TokenResponse
    {
        return $this->requestService->requestToken($clientId, $secret);
    }

    /**
     * Converts an enum case to its value and keeps scalars as is,
     * so methods can accept both enums and raw API values.
     */
    protected static function value(BackedEnum|string|int|null $value): string|int|null
    {
        return $value instanceof BackedEnum ? $value->value : $value;
    }

    /**
     * Same as {@see self::value()} for a list of values.
     *
     * @param  array<BackedEnum|string|int>  $values
     * @return list<string|int>
     */
    protected static function values(array $values): array
    {
        return array_values(array_map(static::value(...), $values));
    }
}
