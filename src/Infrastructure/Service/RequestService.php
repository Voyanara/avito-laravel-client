<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Service;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Domain\ValueObjects\RequestAttachVO;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Presentation\Responses\TokenResponse;

class RequestService
{
    protected ?TokenStorageInterface $tokenStorage = null;
    protected ?string $clientId = null;
    protected ?string $clientSecret = null;
    protected ?string $apiUrl = null;

    /**
     * Устанавливает данные для обновления токена
     */
    public function setTokenCredentials(
        TokenStorageInterface $tokenStorage,
        string $clientId,
        string $clientSecret,
        string $apiUrl = null
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
        return $this->tokenStorage !== null && $this->clientId !== null && $this->clientSecret !== null;
    }

    /**
     * Обновляет токен и возвращает новое значение
     *
     * @return string
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

        $tokenUrl = ($this->apiUrl ?? '') . '/token';
        $response = $this->executeRequest($tokenUrl, 'POST', $data, false);
        $tokenResponse = TokenResponse::from($response);
        
        $this->tokenStorage->set($tokenResponse, $this->clientId, $this->clientSecret);
        
        return $tokenResponse->accessToken;
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
        Log::info('Info request '.$url);
        $headers = [
            'accept' => 'application/json',
        ];

        if ($isTokenRequired && $token) {
            $headers['Authorization'] = 'Bearer '.$token;
        }

        /**
         * @var Response $response
         */
        if ($method === 'POST' && $asJson) {
            $response = Http::withHeaders($headers)->{$method}($url, $data);
        } elseif ($method === 'POST' && $withAttach && $attachData) {
            $response = Http::attach($attachData->name, $attachData->content, $attachData->filename)
                ->withHeaders($headers)->{$method}($url, $data);
        } else {
            $response = Http::asForm()->withHeaders($headers)->{$method}($url, $data);
        }

        if ($response->json('error')) {
            throw new ClientResponseException($response->body());
        }

        if (($response->status() === 401 || $response->status() === 403) && $isTokenRequired) {
            throw new TokenValidException($response->body());
        }

        if ($response->failed()) {
            throw new ClientResponseException($response->body());
        }

        return $response->json();
    }
}
