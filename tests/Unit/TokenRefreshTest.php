<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Infrastructure\Service\RequestService;

/**
 * @return list<string>
 */
function recordedUrls(): array
{
    return Http::recorded()->map(fn (array $pair) => $pair[0]->url())->values()->all();
}

function lastAuthorization(string $url): ?string
{
    $request = Http::recorded()->map(fn (array $pair) => $pair[0])
        ->filter(fn (Request $request) => $request->url() === $url)
        ->last();

    return $request?->header('Authorization')[0] ?? null;
}

const SELF_URL = 'https://api.avito.ru/core/v1/accounts/self';
const TOKEN_URL = 'https://api.avito.ru/token';

it('shares one request service between repositories', function () {
    expect(app(RequestService::class))->toBe(app(RequestService::class))
        ->and(app(Client::class)->user())->not->toBeNull();
});

it('uses the token refreshed by another repository without requesting a new one', function () {
    storeToken('old-token');

    Http::fake([
        'api.avito.ru/token' => Http::response(avitoToken('new-token')),
        'api.avito.ru/core/v1/accounts/self' => Http::response(avitoSelf()),
        'api.avito.ru/messenger/v2/accounts/42/chats*' => Http::sequence()
            ->push(['error' => ['code' => 401, 'message' => 'invalid access token']], 401)
            ->push(['chats' => [], 'meta' => []]),
    ]);

    $client = app(Client::class);
    $client->user()->self();
    $client->messenger()->getChats(42);
    $client->user()->self();

    expect(array_count_values(recordedUrls())[TOKEN_URL])->toBe(1)
        ->and(lastAuthorization(SELF_URL))->toBe('Bearer new-token');
});

it('uses the token refreshed by another process instead of requesting a new one', function () {
    storeToken('old-token');

    Http::fake([
        'api.avito.ru/core/v1/accounts/self' => Http::sequence()
            ->push(avitoSelf())
            ->push(['error' => ['code' => 401, 'message' => 'invalid access token']], 401)
            ->push(avitoSelf()),
    ]);

    $client = app(Client::class);
    $client->user()->self();

    // A queue worker on the same storage has refreshed the token meanwhile.
    storeToken('other-process-token');

    expect($client->user()->self()->id)->toBe(42)
        ->and(recordedUrls())->toBe([SELF_URL, SELF_URL, SELF_URL])
        ->and(lastAuthorization(SELF_URL))->toBe('Bearer other-process-token');
});

it('does not request another token when a freshly issued one is rejected', function () {
    Http::fake([
        'api.avito.ru/token' => Http::response(avitoToken('fresh-token')),
        'api.avito.ru/core/v1/accounts/self' => Http::response(['error' => ['code' => 403, 'message' => 'insufficient scope']], 403),
    ]);

    $client = app(Client::class);

    expect(fn () => $client->user()->self())->toThrow(TokenValidException::class)
        ->and(fn () => $client->user()->self())->toThrow(TokenValidException::class)
        ->and(recordedUrls())->toBe([TOKEN_URL, SELF_URL, SELF_URL]);
});

it('refreshes a token issued long ago when it expires', function () {
    Http::fake([
        'api.avito.ru/token' => Http::sequence()
            ->push(avitoToken('first-token'))
            ->push(avitoToken('second-token')),
        'api.avito.ru/core/v1/accounts/self' => Http::sequence()
            ->push(avitoSelf())
            ->push(['error' => ['code' => 401, 'message' => 'invalid access token']], 401)
            ->push(avitoSelf()),
    ]);

    $client = app(Client::class);
    $client->user()->self();

    $this->travel(25)->hours();

    expect($client->user()->self()->id)->toBe(42)
        ->and(recordedUrls())->toBe([TOKEN_URL, SELF_URL, SELF_URL, TOKEN_URL, SELF_URL])
        ->and(lastAuthorization(SELF_URL))->toBe('Bearer second-token')
        ->and(app(TokenStorageInterface::class)->get('test-client', 'test-secret'))->toBe('second-token');
});
