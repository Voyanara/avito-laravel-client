<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Voyanara\LaravelApiClient\Application\Facades\AvitoClientFacade;
use Voyanara\LaravelApiClient\Application\Facades\Client;

beforeEach(function () {
    storeToken('valid-token');
});

it('returns the authorized account', function () {
    Http::fake([
        'api.avito.ru/core/v1/accounts/self' => Http::response(avitoSelf()),
    ]);

    $me = app(Client::class)->user()->self();

    expect($me->id)->toBe(42)
        ->and($me->name)->toBe('Test Seller')
        ->and($me->profileUrl)->toBe('https://www.avito.ru/user/42/profile');

    Http::assertSent(fn (Request $request) => $request->method() === 'GET'
        && $request->hasHeader('Authorization', 'Bearer valid-token'));
});

it('works through the facade', function () {
    Http::fake([
        'api.avito.ru/core/v1/accounts/self' => Http::response(avitoSelf()),
    ]);

    expect(AvitoClientFacade::user()->self()->id)->toBe(42);
});

it('returns the balance', function () {
    Http::fake([
        'api.avito.ru/core/v1/accounts/42/balance*' => Http::response(['real' => 1500.5, 'bonus' => 100]),
    ]);

    $balance = app(Client::class)->user()->getBalance(42);

    expect($balance->real)->toBe(1500.5)
        ->and($balance->bonus)->toEqual(100.0);
});

it('returns the operations history', function () {
    Http::fake([
        'api.avito.ru/core/v1/accounts/operations_history' => Http::response([
            'result' => [
                'operations' => [[
                    'amountBonus' => 0,
                    'amountRub' => 99,
                    'amountTotal' => 99,
                    'itemId' => 1001,
                    'operationName' => 'Promotion',
                    'operationType' => 'payment',
                    'paidAt' => '2026-11-20T10:00:00+03:00',
                    'serviceId' => 1,
                    'serviceName' => 'XL',
                    'serviceType' => 'vas',
                    'updatedAt' => '2026-11-20T10:00:00+03:00',
                ]],
            ],
        ]),
    ]);

    $history = app(Client::class)->user()->getOperationsHistory('2026-11-14T00:00:00+03:00', '2026-11-21T00:00:00+03:00');

    expect($history->operations)->toHaveCount(1)
        ->and($history->operations->first()->amountTotal)->toEqual(99.0)
        ->and($history->operations->sum('amountTotal'))->toEqual(99);

    Http::assertSent(fn (Request $request) => $request->method() === 'POST'
        && $request['dateTimeFrom'] === '2026-11-14T00:00:00+03:00'
        && $request['dateTimeTo'] === '2026-11-21T00:00:00+03:00');
});
