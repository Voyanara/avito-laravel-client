<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Domain\Enums\Items\VasServiceEnum;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TooManyRequestsException;

beforeEach(function () {
    storeToken('valid-token');
    Sleep::fake();
});

/**
 * Runs the callback and returns the exception it throws.
 */
function caught(Closure $callback): Throwable
{
    try {
        $callback();
    } catch (Throwable $e) {
        return $e;
    }

    throw new RuntimeException('Exception was not thrown');
}

it('exposes the HTTP status and the Avito error', function () {
    Http::fake([
        'api.avito.ru/messenger/v1/accounts/42/chats/chat-1/messages' => Http::response(
            ['error' => ['code' => 402, 'message' => 'Перейдите на подписку с API мессенджера']],
            402,
        ),
    ]);

    $e = caught(fn () => app(Client::class)->messenger()->sendMessage(42, 'chat-1', 'Hi'));

    expect($e)->toBeInstanceOf(ClientResponseException::class)
        ->and($e->status)->toBe(402)
        ->and($e->getCode())->toBe(402)
        ->and($e->errorCode)->toBe(402)
        ->and($e->errorMessage)->toBe('Перейдите на подписку с API мессенджера')
        ->and($e->retryAfter)->toBeNull()
        ->and($e->getMessage())->toContain('"code":402');
});

it('exposes promotion error codes', function () {
    Http::fake([
        'api.avito.ru/core/v2/items/1001/vas/' => Http::response(['code' => 1009, 'message' => 'not enough money'], 400),
    ]);

    $e = caught(fn () => app(Client::class)->items()->applyVas(1001, [VasServiceEnum::XL]));

    expect($e->status)->toBe(400)
        ->and($e->errorCode)->toBe(1009)
        ->and($e->errorMessage)->toBe('not enough money');
});

it('keeps the old exception constructor working', function () {
    $e = new ClientResponseException('raw body');

    expect($e->getMessage())->toBe('raw body')
        ->and($e->status)->toBeNull()
        ->and($e->errorCode)->toBeNull();
});

it('does not let subclasses change the constructor that fromResponse() relies on', function () {
    expect((new ReflectionMethod(ClientResponseException::class, '__construct'))->isFinal())->toBeTrue();
});

it('repeats a GET request after Retry-After on 429', function () {
    Http::fake([
        'api.avito.ru/core/v1/accounts/self' => Http::sequence()
            ->push(['error' => ['code' => 429, 'message' => 'too many requests']], 429, ['Retry-After' => '2'])
            ->push(avitoSelf()),
    ]);

    expect(app(Client::class)->user()->self()->id)->toBe(42);

    Http::assertSentCount(2);
    Sleep::assertSequence([Sleep::for(2)->seconds()]);
});

it('waits one second when Retry-After is missing', function () {
    Http::fake([
        'api.avito.ru/core/v1/accounts/self' => Http::sequence()
            ->push([], 429)
            ->push(avitoSelf()),
    ]);

    app(Client::class)->user()->self();

    Sleep::assertSequence([Sleep::for(1)->seconds()]);
});

it('understands Retry-After as an HTTP date', function () {
    $this->freezeTime();

    Http::fake([
        'api.avito.ru/core/v1/accounts/self' => Http::sequence()
            ->push([], 429, ['Retry-After' => now()->addSeconds(3)->toRfc7231String()])
            ->push(avitoSelf()),
    ]);

    app(Client::class)->user()->self();

    Sleep::assertSequence([Sleep::for(3)->seconds()]);
});

it('throws right away when Avito asks to wait longer than max_wait', function () {
    Http::fake([
        'api.avito.ru/core/v1/accounts/self' => Http::response([], 429, ['Retry-After' => '60']),
    ]);

    $e = caught(fn () => app(Client::class)->user()->self());

    expect($e)->toBeInstanceOf(TooManyRequestsException::class)
        ->and($e)->toBeInstanceOf(ClientResponseException::class)
        ->and($e->status)->toBe(429)
        ->and($e->retryAfter)->toBe(60);

    Http::assertSentCount(1);
    Sleep::assertNeverSlept();
});

it('gives up after the configured number of retries', function () {
    Http::fake([
        'api.avito.ru/core/v1/accounts/self' => Http::response([], 429, ['Retry-After' => '1']),
    ]);

    expect(caught(fn () => app(Client::class)->user()->self()))->toBeInstanceOf(TooManyRequestsException::class);

    Http::assertSentCount(2);
    Sleep::assertSleptTimes(1);
});

it('never repeats POST requests', function () {
    Http::fake([
        'api.avito.ru/messenger/v1/accounts/42/chats/chat-1/messages' => Http::response([], 429, ['Retry-After' => '1']),
    ]);

    expect(caught(fn () => app(Client::class)->messenger()->sendMessage(42, 'chat-1', 'Hi')))
        ->toBeInstanceOf(TooManyRequestsException::class);

    Http::assertSentCount(1);
    Sleep::assertNeverSlept();
});

it('never repeats PUT requests', function () {
    Http::fake([
        'api.avito.ru/core/v2/items/1001/vas/' => Http::response([], 429, ['Retry-After' => '1']),
    ]);

    expect(caught(fn () => app(Client::class)->items()->applyVas(1001, [VasServiceEnum::XL])))
        ->toBeInstanceOf(TooManyRequestsException::class);

    Http::assertSentCount(1);
    Sleep::assertNeverSlept();
});

it('does not retry when retries are disabled', function () {
    config()->set('avito.retry.times', 0);

    Http::fake([
        'api.avito.ru/core/v1/accounts/self' => Http::response([], 429, ['Retry-After' => '1']),
    ]);

    expect(caught(fn () => app(Client::class)->user()->self()))->toBeInstanceOf(TooManyRequestsException::class);

    Http::assertSentCount(1);
    Sleep::assertNeverSlept();
});
