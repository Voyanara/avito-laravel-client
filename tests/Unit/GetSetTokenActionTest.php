<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Voyanara\LaravelApiClient\Application\Actions\GetSetTokenAction;
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
