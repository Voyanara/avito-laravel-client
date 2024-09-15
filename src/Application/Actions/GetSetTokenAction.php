<?php

namespace Voyanara\LaravelApiClient\Application\Actions;

use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\TokenHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\TokenResponse;

readonly class GetSetTokenAction
{
    public function __construct(
        protected TokenStorageInterface $tokenStorage,
        protected TokenHttpRepository $tokenHttpRepository
    ) {}

    public function set(TokenResponse $tokenResponse, string $clientId, string $secret): void
    {
        $this->tokenStorage->set($tokenResponse, $clientId, $secret);
    }

    public function getFromStorage(string $clientId, string $secret): ?string
    {
        return $this->tokenStorage->get($clientId, $secret);
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getFromExternal(): TokenResponse
    {
        return $this->tokenHttpRepository->getToken($this->tokenHttpRepository->clientId, $this->tokenHttpRepository->secret);
    }
}
