<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Service;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;
use Voyanara\LaravelApiClient\Domain\Enums\GrantTypesEnum;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TooManyRequestsException;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Domain\ValueObjects\RequestAttachVO;
use Voyanara\LaravelApiClient\Presentation\Responses\TokenResponse;

/**
 * Bound as a singleton: all repositories share one instance and therefore one access token.
 */
class RequestService
{
    /**
     * A token issued less than this many seconds ago is not expired: if Avito rejects it,
     * the reason is something else (e.g. a missing scope) and requesting another token won't help.
     */
    protected const int FRESH_TOKEN_SECONDS = 60;

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
     * Token this instance received from /token and when, see FRESH_TOKEN_SECONDS.
     */
    protected ?string $issuedToken = null;

    protected ?int $issuedAt = null;

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
     * @throws TooManyRequestsException
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
        $canRefresh = $isTokenRequired && $this->canRefreshToken();

        if ($canRefresh) {
            $token = $this->currentToken();
        }

        try {
            return $this->executeWithRetry($url, $method, $data, $isTokenRequired, $token, $asJson, $withAttach, $attachData);
        } catch (TokenValidException $e) {
            if (! $canRefresh) {
                throw $e;
            }

            $newToken = $this->tokenAfterRejection($token, $e);

            return $this->executeWithRetry($url, $method, $data, $isTokenRequired, $newToken, $asJson, $withAttach, $attachData);
        }
    }

    /**
     * Requests a new client_credentials token. Does not store it.
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function requestToken(?string $clientId = null, ?string $clientSecret = null): TokenResponse
    {
        $data = [
            'grant_type' => GrantTypesEnum::CLIENT_CREDENTIALS->value,
            'client_id' => $clientId ?? $this->clientId,
            'client_secret' => $clientSecret ?? $this->clientSecret,
        ];

        return TokenResponse::from($this->executeRequest(($this->apiUrl ?? '').'/token', 'POST', $data, false));
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
     * Picks the token to retry with after Avito rejected $rejected with 401/403:
     *
     * - a token we have just received is not expired, so the error is rethrown;
     * - a different token in storage means another repository or process has already refreshed it;
     * - otherwise a new token is requested.
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    protected function tokenAfterRejection(?string $rejected, TokenValidException $e): string
    {
        if ($rejected !== null && $rejected === $this->issuedToken && now()->getTimestamp() - $this->issuedAt < static::FRESH_TOKEN_SECONDS) {
            throw $e;
        }

        $stored = $this->tokenStorage->get($this->clientId, $this->clientSecret);

        if ($stored && $stored !== $rejected) {
            return $this->accessToken = $stored;
        }

        return $this->refreshToken();
    }

    /**
     * Requests a new token, saves it to the storage and uses it for the following requests.
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function renewToken(): TokenResponse
    {
        $tokenResponse = $this->requestToken();

        $this->tokenStorage->set($tokenResponse, $this->clientId, $this->clientSecret);

        $this->accessToken = $this->issuedToken = $tokenResponse->accessToken;
        $this->issuedAt = now()->getTimestamp();

        return $tokenResponse;
    }

    /**
     * Обновляет токен и возвращает новое значение
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    protected function refreshToken(): string
    {
        return $this->renewToken()->accessToken;
    }

    /**
     * Repeats GET requests rejected with 429, waiting for Retry-After. Other methods are never
     * repeated: a failed POST/PUT (sending a message, buying a service) may still have been applied.
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     * @throws TooManyRequestsException
     */
    protected function executeWithRetry(string $url,
        string $method,
        array $data,
        bool $isTokenRequired,
        ?string $token,
        bool $asJson,
        bool $withAttach,
        ?RequestAttachVO $attachData,
    ): array {
        $retries = $method === 'GET' ? max(0, (int) config('avito.retry.times', 1)) : 0;
        $maxWait = max(0, (int) config('avito.retry.max_wait', 5));

        for ($attempt = 0; ; $attempt++) {
            try {
                return $this->executeRequest($url, $method, $data, $isTokenRequired, $token, $asJson, $withAttach, $attachData);
            } catch (TooManyRequestsException $e) {
                $wait = $e->retryAfter ?? 1;

                if ($attempt >= $retries || $wait > $maxWait) {
                    throw $e;
                }

                Sleep::for($wait)->seconds();
            }
        }
    }

    /**
     * Выполняет HTTP запрос
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     * @throws TooManyRequestsException
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

        $request = Http::withHeaders($headers)
            ->timeout((int) config('avito.timeout', 30))
            ->connectTimeout((int) config('avito.connect_timeout', 10));

        if ($asJson && in_array($method, ['POST', 'PUT'], true)) {
            $response = $request->{$method}($url, $data);
        } elseif ($method === 'POST' && $withAttach && $attachData) {
            $response = $request->attach($attachData->name, $attachData->content, $attachData->filename)
                ->{$method}($url, $data);
        } else {
            $response = $request->asForm()->{$method}($url, $data);
        }

        $this->throwIfFailed($response, $isTokenRequired);

        return $response->json() ?? [];
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     * @throws TooManyRequestsException
     */
    protected function throwIfFailed(Response $response, bool $isTokenRequired): void
    {
        // Check auth errors first: Avito may return an "error" field together with 401/403,
        // and the token must be refreshed in that case instead of failing right away.
        if (($response->status() === 401 || $response->status() === 403) && $isTokenRequired) {
            throw TokenValidException::fromResponse($response);
        }

        if ($response->status() === 429) {
            throw TooManyRequestsException::fromResponse($response);
        }

        if ($response->failed() || $response->json('error')) {
            throw ClientResponseException::fromResponse($response);
        }
    }
}
