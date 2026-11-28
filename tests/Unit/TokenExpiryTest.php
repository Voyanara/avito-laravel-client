<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Voyanara\LaravelApiClient\Application\Facades\Client;

/**
 * Requests in the order they were sent, as "url Authorization".
 *
 * @return list<string>
 */
function sentWithAuthorization(): array
{
    return Http::recorded()
        ->map(fn (array $pair) => trim($pair[0]->url().' '.($pair[0]->header('Authorization')[0] ?? '')))
        ->values()
        ->all();
}

function fakeHourTokens(): void
{
    Http::fake([
        'api.avito.ru/token' => Http::sequence()
            ->push(['access_token' => 'first-token', 'expires_in' => 3600, 'token_type' => 'Bearer'])
            ->push(['access_token' => 'second-token', 'expires_in' => 3600, 'token_type' => 'Bearer']),
        'api.avito.ru/core/v1/accounts/self' => Http::response(avitoSelf()),
    ]);
}

it('renews a token issued by this instance before it expires', function () {
    fakeHourTokens();

    $client = app(Client::class);
    $client->user()->self();

    $this->travel(3570)->seconds();

    expect($client->user()->self()->id)->toBe(42)
        ->and(sentWithAuthorization())->toBe([
            'https://api.avito.ru/token',
            'https://api.avito.ru/core/v1/accounts/self Bearer first-token',
            'https://api.avito.ru/token',
            'https://api.avito.ru/core/v1/accounts/self Bearer second-token',
        ]);
});

it('keeps a token issued by this instance while it is far from expiry', function () {
    fakeHourTokens();

    $client = app(Client::class);
    $client->user()->self();

    $this->travel(3000)->seconds();

    $client->user()->self();

    expect(sentWithAuthorization())->toBe([
        'https://api.avito.ru/token',
        'https://api.avito.ru/core/v1/accounts/self Bearer first-token',
        'https://api.avito.ru/core/v1/accounts/self Bearer first-token',
    ]);
});

it('does not renew a token read from storage proactively', function () {
    storeToken('stored-token');
    fakeHourTokens();

    $client = app(Client::class);
    $client->user()->self();

    $this->travel(86390)->seconds();

    $client->user()->self();

    Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/token'));

    expect(sentWithAuthorization())->toBe([
        'https://api.avito.ru/core/v1/accounts/self Bearer stored-token',
        'https://api.avito.ru/core/v1/accounts/self Bearer stored-token',
    ]);
});
