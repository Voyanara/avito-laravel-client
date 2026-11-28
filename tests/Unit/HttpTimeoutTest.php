<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Voyanara\LaravelApiClient\Application\Facades\Client;

/**
 * Fakes the account endpoint and returns a closure that gives back the Guzzle options of the last request.
 */
function fakeSelfCapturingOptions(): Closure
{
    $captured = new ArrayObject;

    Http::fake([
        'api.avito.ru/token' => Http::response(avitoToken()),
        'api.avito.ru/core/v1/accounts/self' => function (Request $request, array $options) use ($captured) {
            $captured->exchangeArray($options);

            return Http::response(avitoSelf());
        },
    ]);

    return fn (): array => $captured->getArrayCopy();
}

it('uses 30 seconds to respond and 10 seconds to connect by default', function () {
    $options = fakeSelfCapturingOptions();

    app(Client::class)->user()->self();

    expect(config('avito.timeout'))->toBe(30)
        ->and(config('avito.connect_timeout'))->toBe(10)
        ->and($options()['timeout'])->toBe(30)
        ->and($options()['connect_timeout'])->toBe(10);
});

it('uses the configured HTTP timeouts', function () {
    config()->set('avito.timeout', 7);
    config()->set('avito.connect_timeout', 3);

    $options = fakeSelfCapturingOptions();

    app(Client::class)->user()->self();

    expect($options()['timeout'])->toBe(7)
        ->and($options()['connect_timeout'])->toBe(3);
});
