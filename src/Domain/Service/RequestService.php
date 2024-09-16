<?php

namespace Voyanara\LaravelApiClient\Domain\Service;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;

class RequestService
{
    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function sendRequest(string $url, string $method = 'GET', array $data = [], bool $isTokenRequired = true, ?string $token = null, bool $asJson = false): array
    {
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
