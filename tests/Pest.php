<?php

use Illuminate\Http\Client\Request;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Presentation\Responses\TokenResponse;
use Voyanara\LaravelApiClient\Tests\TestCase;
use Voyanara\LaravelApiClient\Tests\Unit\TestCase as UnitTestCase;

/*
|--------------------------------------------------------------------------
| Test cases
|--------------------------------------------------------------------------
| Unit — offline tests with a faked Avito API (run in CI).
| Application — live tests against the real Avito API (need credentials).
*/

uses(TestCase::class)->in('Application');
uses(UnitTestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Helpers for offline tests
|--------------------------------------------------------------------------
*/

function avitoToken(string $token = 'test-token'): array
{
    return [
        'access_token' => $token,
        'expires_in' => 86400,
        'token_type' => 'Bearer',
    ];
}

/**
 * Puts a token into the configured storage, as if it was fetched earlier.
 */
function storeToken(string $token): void
{
    app(TokenStorageInterface::class)->set(
        TokenResponse::from(avitoToken($token)),
        'test-client',
        'test-secret',
    );
}

function avitoSelf(): array
{
    return [
        'id' => 42,
        'name' => 'Test Seller',
        'email' => 'seller@example.com',
        'phone' => '+79990000000',
        'profile_url' => 'https://www.avito.ru/user/42/profile',
    ];
}

function avitoChat(string $id = 'chat-1'): array
{
    return [
        'id' => $id,
        'context' => [
            'type' => 'item',
            'value' => [
                'id' => 1001,
                'images' => [],
                'price_string' => '1 000 ₽',
                'status_id' => 10,
                'title' => 'Sofa',
                'url' => 'https://www.avito.ru/item/1001',
                'user_id' => 42,
            ],
        ],
        'created' => 1700000000,
        'updated' => 1700000100,
        'users' => [],
        'last_message' => ['id' => 'msg-1'],
    ];
}

function avitoSentMessage(): array
{
    return [
        'id' => 'msg-2',
        'author_id' => '42',
        'created' => 1700000200,
        'direction' => 'out',
        'type' => 'text',
        'content' => ['text' => 'Hi'],
    ];
}

/**
 * Query string parameters of a faked request.
 */
function queryOf(Request $request): array
{
    parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

    return $query;
}
