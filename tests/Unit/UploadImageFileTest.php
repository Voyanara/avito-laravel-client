<?php

use Illuminate\Support\Facades\Http;
use Voyanara\LaravelApiClient\Application\Facades\Client;

beforeEach(function () {
    storeToken('valid-token');
});

it('throws a clear error when the image file does not exist', function () {
    Http::fake();

    $path = sys_get_temp_dir().'/avito-missing-'.uniqid().'.jpg';

    expect(fn () => app(Client::class)->messenger()->uploadImage(42, $path))
        ->toThrow(InvalidArgumentException::class, 'Image file not found or not readable: '.$path);

    Http::assertNothingSent();
});

it('throws a clear error when the path is a directory', function () {
    Http::fake();

    expect(fn () => app(Client::class)->messenger()->uploadImage(42, sys_get_temp_dir()))
        ->toThrow(InvalidArgumentException::class, 'Image file not found or not readable');

    Http::assertNothingSent();
});
