<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientCredentialsException;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;

it('loads the package config without publishing it', function () {
    expect(config('avito.api_url'))->toBe('https://api.avito.ru')
        ->and(config('avito.token_storage'))->toBe('file')
        ->and(config('avito.file_name'))->toBe('avito-token');
});

it('makes no HTTP requests when the client is resolved', function () {
    Http::fake();

    app(Client::class);

    Http::assertNothingSent();
});

it('throws when credentials are missing', function () {
    config()->set('avito.client_id', null);

    app(Client::class);
})->throws(ClientCredentialsException::class);

it('fetches a token on the first call and caches it', function () {
    Http::fake([
        'api.avito.ru/token' => Http::response(avitoToken('fresh-token')),
        'api.avito.ru/core/v1/accounts/self' => Http::response(avitoSelf()),
    ]);

    $client = app(Client::class);
    $client->user()->self();
    $client->user()->self();

    Http::assertSentCount(3);
    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.avito.ru/token'
        && $request['grant_type'] === 'client_credentials'
        && $request['client_id'] === 'test-client'
        && $request['client_secret'] === 'test-secret');

    expect(app(TokenStorageInterface::class)->get('test-client', 'test-secret'))->toBe('fresh-token');
});

it('uses a stored token without requesting a new one', function () {
    storeToken('stored-token');

    Http::fake([
        'api.avito.ru/core/v1/accounts/self' => Http::response(avitoSelf()),
    ]);

    app(Client::class)->user()->self();

    Http::assertSentCount(1);
    Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer stored-token'));
});

it('refreshes an expired token once and reuses the new one', function () {
    storeToken('expired-token');

    Http::fake([
        'api.avito.ru/token' => Http::response(avitoToken('new-token')),
        'api.avito.ru/core/v1/accounts/self' => Http::sequence()
            ->push(['error' => ['code' => 401, 'message' => 'invalid access token']], 401)
            ->push(avitoSelf())
            ->push(avitoSelf()),
    ]);

    $client = app(Client::class);
    $client->user()->self();
    $client->user()->self();

    $urls = Http::recorded()->map(fn (array $pair) => $pair[0]->url())->all();
    $auth = Http::recorded()->map(fn (array $pair) => $pair[0]->header('Authorization')[0] ?? null)->all();

    expect($urls)->toBe([
        'https://api.avito.ru/core/v1/accounts/self',
        'https://api.avito.ru/token',
        'https://api.avito.ru/core/v1/accounts/self',
        'https://api.avito.ru/core/v1/accounts/self',
    ])->and($auth[0])->toBe('Bearer expired-token')
        ->and($auth[2])->toBe('Bearer new-token')
        ->and($auth[3])->toBe('Bearer new-token')
        ->and(app(TokenStorageInterface::class)->get('test-client', 'test-secret'))->toBe('new-token');
});

it('refreshes the token on 403 as well', function () {
    storeToken('expired-token');

    Http::fake([
        'api.avito.ru/token' => Http::response(avitoToken('new-token')),
        'api.avito.ru/core/v1/accounts/self' => Http::sequence()
            ->push(['result' => ['message' => 'invalid access token']], 403)
            ->push(avitoSelf()),
    ]);

    expect(app(Client::class)->user()->self()->id)->toBe(42);

    Http::assertSentCount(3);
});

it('throws TokenValidException when the token is rejected after a refresh', function () {
    Http::fake([
        'api.avito.ru/token' => Http::response(avitoToken()),
        'api.avito.ru/core/v1/accounts/self' => Http::response(['error' => ['code' => 401]], 401),
    ]);

    try {
        app(Client::class)->user()->self();
        $this->fail('Exception was not thrown');
    } catch (TokenValidException $e) {
        expect($e)->toBeInstanceOf(ClientResponseException::class);
    }
});

it('throws ClientResponseException on API errors', function (int $status) {
    storeToken('valid-token');

    Http::fake([
        'api.avito.ru/core/v1/accounts/self' => Http::response(['error' => ['code' => $status, 'message' => 'Error']], $status),
    ]);

    app(Client::class)->user()->self();
})->with([402, 404, 500])->throws(ClientResponseException::class);

it('throws ClientResponseException when the body contains an error field', function () {
    storeToken('valid-token');

    Http::fake([
        'api.avito.ru/core/v1/accounts/self' => Http::response(['error' => ['message' => 'Something went wrong']]),
    ]);

    app(Client::class)->user()->self();
})->throws(ClientResponseException::class, 'Something went wrong');
