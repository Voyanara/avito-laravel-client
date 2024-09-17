<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Repositories;

use Voyanara\LaravelApiClient\Domain\Enums\GrantTypesEnum;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientCredentialsException;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Infrastructure\Service\RequestService;
use Voyanara\LaravelApiClient\Presentation\Responses\TokenResponse;

abstract class BaseHttpRepository
{
    /**
     * @throws ClientCredentialsException
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function __construct(
        public string $apiUrl,
        public readonly ?string $clientId,
        public readonly ?string $secret,
        public readonly TokenStorageInterface $tokenStorage,
        public readonly RequestService $requestService,
        public ?string $token = null,
    ) {
        $this->init();
    }

    /**
     * @throws ClientCredentialsException
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    protected function init(): void
    {
        if (! $this->clientId || ! $this->secret) {
            throw new ClientCredentialsException('Client id and secret must be set');
        }

        $this->token = $this->tokenStorage->get($this->clientId, $this->secret);

        if (! $this->token) {

            $tokenDTO = $this->getToken($this->clientId, $this->secret);
            $this->tokenStorage->set($tokenDTO, $this->clientId, $this->secret);
            $this->token = $tokenDTO->accessToken;
        }
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getToken(string $clientId, string $secret): TokenResponse
    {
        $data = [
            'grant_type' => GrantTypesEnum::CLIENT_CREDENTIALS->value,
            'client_id' => $clientId,
            'client_secret' => $secret,
        ];

        $response = $this->requestService->sendRequest($this->apiUrl.'/token', 'POST', $data, false);

        return TokenResponse::from($response);
    }
}
