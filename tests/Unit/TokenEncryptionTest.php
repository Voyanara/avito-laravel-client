<?php

use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;

function tokenFileKey(): string
{
    return md5('test-client').'::'.md5('test-secret');
}

function useDatabaseStorage(): void
{
    config()->set('avito.token_storage', 'database');
    test()->loadMigrationsFrom(__DIR__.'/../../database/migrations');
}

/**
 * Simulates a rotated APP_KEY: values encrypted earlier can no longer be decrypted.
 */
function rotateAppKey(): void
{
    $encrypter = new Encrypter(Encrypter::generateKey('aes-256-cbc'), 'aes-256-cbc');
    app()->instance('encrypter', $encrypter);
    Crypt::swap($encrypter);
}

describe('file storage', function () {
    it('stores the token encrypted', function () {
        storeToken('secret-file-token');

        $raw = Storage::disk('local')->get('avito_module/avito-token.json');
        $stored = json_decode($raw, true)[tokenFileKey()];

        expect($raw)->not->toContain('secret-file-token')
            ->and(Crypt::decryptString($stored))->toBe('secret-file-token')
            ->and(app(TokenStorageInterface::class)->get('test-client', 'test-secret'))->toBe('secret-file-token');
    });

    it('treats a plain text token of an older version as missing', function () {
        Storage::disk('local')->put('avito_module/avito-token.json', json_encode([tokenFileKey() => 'legacy-file-token']));

        expect(app(TokenStorageInterface::class)->get('test-client', 'test-secret'))->toBeNull();
    });

    it('treats a token encrypted with a rotated app key as missing', function () {
        storeToken('rotated-file-token');

        rotateAppKey();

        expect(app(TokenStorageInterface::class)->get('test-client', 'test-secret'))->toBeNull();
    });

    it('requests a new token instead of sending an unreadable one', function () {
        Storage::disk('local')->put('avito_module/avito-token.json', json_encode([tokenFileKey() => 'legacy-file-token']));

        Http::fake([
            'api.avito.ru/token' => Http::response(avitoToken('new-token')),
            'api.avito.ru/core/v1/accounts/self' => Http::response(avitoSelf()),
        ]);

        app(Client::class)->user()->self();

        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/token'));
        Http::assertNotSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer legacy-file-token'));
    });
});

describe('database storage', function () {
    it('stores the token encrypted', function () {
        useDatabaseStorage();

        storeToken('secret-db-token');

        $raw = DB::table('avito_module_clients')->value('access_token');

        expect($raw)->not->toContain('secret-db-token')
            ->and(Crypt::decryptString($raw))->toBe('secret-db-token')
            ->and(app(TokenStorageInterface::class)->get('test-client', 'test-secret'))->toBe('secret-db-token');
    });

    it('updates the encrypted token of an existing client', function () {
        useDatabaseStorage();

        storeToken('first-db-token');
        storeToken('second-db-token');

        expect(DB::table('avito_module_clients')->count())->toBe(1)
            ->and(app(TokenStorageInterface::class)->get('test-client', 'test-secret'))->toBe('second-db-token');
    });

    it('treats a plain text token of an older version as missing', function () {
        useDatabaseStorage();

        DB::table('avito_module_clients')->insert([
            'id' => 'b3f1c2d4-0000-4000-8000-000000000001',
            'client_id' => md5('test-client'),
            'client_secret' => md5('test-secret'),
            'grant_type' => 'client_credentials',
            'expires_in' => 86400,
            'access_token' => 'legacy-db-token',
        ]);

        expect(app(TokenStorageInterface::class)->get('test-client', 'test-secret'))->toBeNull();
    });
});
