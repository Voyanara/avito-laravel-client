<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Voyanara\LaravelApiClient\Application\Actions\GetSetTokenAction;
use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Presentation\Responses\TokenResponse;

it('saves a token and reads it back from storage', function () {
    $action = app(GetSetTokenAction::class);

    expect($action->getFromStorage('client', 'secret'))->toBeNull();

    $action->set(TokenResponse::from(avitoToken('saved-token')), 'client', 'secret');

    expect($action->getFromStorage('client', 'secret'))->toBe('saved-token')
        ->and($action->getFromStorage('client', 'other-secret'))->toBeNull();
});

it('requests a new token from Avito', function () {
    Http::fake([
        'api.avito.ru/token' => Http::response(avitoToken('external-token')),
    ]);

    $token = app(GetSetTokenAction::class)->getFromExternal();

    expect($token->accessToken)->toBe('external-token')
        ->and($token->expiresIn)->toBe(86400)
        ->and($token->tokenType)->toBe('Bearer');

    Http::assertSent(fn (Request $request) => $request->method() === 'POST'
        && $request['grant_type'] === 'client_credentials'
        && $request['client_id'] === 'test-client'
        && $request['client_secret'] === 'test-secret');
});

it('stores the token received from Avito and uses it for the next requests', function () {
    storeToken('old-token');

    Http::fake([
        'api.avito.ru/token' => Http::response(avitoToken('external-token')),
        'api.avito.ru/core/v1/accounts/self' => Http::response(avitoSelf()),
    ]);

    $client = app(Client::class);
    $client->user()->self();

    app(GetSetTokenAction::class)->getFromExternal();

    $client->user()->self();

    $selfAuthorization = Http::recorded()
        ->map(fn (array $pair) => $pair[0])
        ->filter(fn (Request $request) => str_ends_with($request->url(), '/core/v1/accounts/self'))
        ->map(fn (Request $request) => $request->header('Authorization')[0])
        ->values()
        ->all();

    expect(app(TokenStorageInterface::class)->get('test-client', 'test-secret'))->toBe('external-token')
        ->and($selfAuthorization)->toBe(['Bearer old-token', 'Bearer external-token']);
});
