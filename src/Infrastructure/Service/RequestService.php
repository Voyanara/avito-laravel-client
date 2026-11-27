<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Service;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Domain\ValueObjects\RequestAttachVO;
use Voyanara\LaravelApiClient\Presentation\Responses\TokenResponse;

class RequestService
{
    protected ?TokenStorageInterface $tokenStorage = null;

    protected ?string $clientId = null;

    protected ?string $clientSecret = null;

    protected ?string $apiUrl = null;

    /**
     * Access token currently in use. Kept in memory so that, after a refresh,
     * later requests use the new token instead of the expired one.
     */
    protected ?string $accessToken = null;

    /**
     * Устанавливает данные для обновления токена
     */
    public function setTokenCredentials(
        TokenStorageInterface $tokenStorage,
        string $clientId,
        string $clientSecret,
        ?string $apiUrl = null
    ): void {
        $this->tokenStorage = $tokenStorage;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->apiUrl = $apiUrl;
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function sendRequest(string $url,
        string $method = 'GET',
        array $data = [],
        bool $isTokenRequired = true,
        ?string $token = null,
        bool $asJson = false,
        bool $withAttach = false,
        ?RequestAttachVO $attachData = null,
    ): array {
        if ($isTokenRequired && $this->canRefreshToken()) {
            $token = $this->currentToken();
        }

        try {
            return $this->executeRequest($url, $method, $data, $isTokenRequired, $token, $asJson, $withAttach, $attachData);
        } catch (TokenValidException $e) {
            if ($isTokenRequired && $this->canRefreshToken()) {
                $newToken = $this->refreshToken();

                return $this->executeRequest($url, $method, $data, $isTokenRequired, $newToken, $asJson, $withAttach, $attachData);
            }

            throw $e;
        }
    }

    protected function canRefreshToken(): bool
    {
        return $this->tokenStorage instanceof TokenStorageInterface && $this->clientId !== null && $this->clientSecret !== null;
    }

    /**
     * Returns the token in use: from memory, then from storage, otherwise requests a new one.
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    protected function currentToken(): string
    {
        return $this->accessToken ??= $this->tokenStorage->get($this->clientId, $this->clientSecret) ?: $this->refreshToken();
    }

    /**
     * Обновляет токен и возвращает новое значение
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    protected function refreshToken(): string
    {
        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];

        $tokenUrl = ($this->apiUrl ?? '').'/token';
        $response = $this->executeRequest($tokenUrl, 'POST', $data, false);
        $tokenResponse = TokenResponse::from($response);

        $this->tokenStorage->set($tokenResponse, $this->clientId, $this->clientSecret);

        return $this->accessToken = $tokenResponse->accessToken;
    }

    /**
     * Выполняет HTTP запрос
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    protected function executeRequest(string $url,
        string $method = 'GET',
        array $data = [],
        bool $isTokenRequired = true,
        ?string $token = null,
        bool $asJson = false,
        bool $withAttach = false,
        ?RequestAttachVO $attachData = null,
    ): array {
        Log::debug('Avito API request: '.$method.' '.$url);
        $headers = [
            'accept' => 'application/json',
        ];

        if ($isTokenRequired && $token) {
            $headers['Authorization'] = 'Bearer '.$token;
        }

        if ($method === 'POST' && $asJson) {
            $response = Http::withHeaders($headers)->{$method}($url, $data);
        } elseif ($method === 'POST' && $withAttach && $attachData) {
            $response = Http::attach($attachData->name, $attachData->content, $attachData->filename)
                ->withHeaders($headers)->{$method}($url, $data);
        } else {
            $response = Http::asForm()->withHeaders($headers)->{$method}($url, $data);
        }

        // Check auth errors first: Avito may return an "error" field together with 401/403,
        // and the token must be refreshed in that case instead of failing right away.
        if (($response->status() === 401 || $response->status() === 403) && $isTokenRequired) {
            throw new TokenValidException($response->body());
        }

        if ($response->json('error')) {
            throw new ClientResponseException($response->body());
        }

        if ($response->failed()) {
            throw new ClientResponseException($response->body());
        }

        return $response->json() ?? [];
    }
}
