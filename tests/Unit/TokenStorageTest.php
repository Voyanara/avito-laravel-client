<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Infrastructure\Persistence\Storage\DatabaseStorage;
use Voyanara\LaravelApiClient\Infrastructure\Persistence\Storage\FileStorage;

it('stores the token in a file by default', function () {
    expect(app(TokenStorageInterface::class))->toBeInstanceOf(FileStorage::class);

    storeToken('file-token');

    Storage::disk('local')->assertExists('avito_module/avito-token.json');
    expect(app(TokenStorageInterface::class)->get('test-client', 'test-secret'))->toBe('file-token')
        ->and(app(TokenStorageInterface::class)->get('other-client', 'test-secret'))->toBeNull();
});

it('stores the token in the database', function () {
    config()->set('avito.token_storage', 'database');
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

    expect(app(TokenStorageInterface::class))->toBeInstanceOf(DatabaseStorage::class);

    Http::fake([
        'api.avito.ru/token' => Http::response(avitoToken('db-token')),
        'api.avito.ru/core/v1/accounts/self' => Http::response(avitoSelf()),
    ]);

    app(Client::class)->user()->self();

    $this->assertDatabaseHas('avito_module_clients', [
        'client_id' => md5('test-client'),
        'client_secret' => md5('test-secret'),
    ]);

    expect(app(TokenStorageInterface::class)->get('test-client', 'test-secret'))->toBe('db-token');
});
