<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Domain\ValueObjects\RequestAttachVO;
use Voyanara\LaravelApiClient\Infrastructure\Service\RequestService;

beforeEach(function () {
    storeToken('valid-token');
});

it('requests the balance without a query string', function () {
    Http::fake([
        'api.avito.ru/core/v1/accounts/42/balance*' => Http::response(['real' => 1500.5, 'bonus' => 100]),
    ]);

    app(Client::class)->user()->getBalance(42);

    Http::assertSent(fn (Request $request) => $request->method() === 'GET'
        && $request->url() === 'https://api.avito.ru/core/v1/accounts/42/balance'
        && queryOf($request) === []);
});

it('sends a multipart request when attach data is given, without the withAttach flag', function () {
    Http::fake([
        'api.avito.ru/upload' => Http::response(['ok' => true]),
    ]);

    $response = app(RequestService::class)->sendRequest(
        'https://api.avito.ru/upload',
        method: 'POST',
        isTokenRequired: false,
        attachData: new RequestAttachVO('file', 'content', 'image.jpg'),
    );

    expect($response)->toBe(['ok' => true]);

    Http::assertSent(fn (Request $request) => $request->isMultipart()
        && $request->hasFile('file', 'content', 'image.jpg'));
});
