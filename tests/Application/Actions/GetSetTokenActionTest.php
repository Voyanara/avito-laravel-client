<?php

namespace Voyanara\LaravelApiClient\Tests\Application\Actions;

use Voyanara\LaravelApiClient\Application\Actions\GetSetTokenAction;
use Voyanara\LaravelApiClient\Presentation\Responses\TokenResponse;
use Voyanara\LaravelApiClient\Tests\TestCase;

class GetSetTokenActionTest extends TestCase
{
    public function test_set(): void
    {

        $action = $this->app->make(GetSetTokenAction::class);
        $client = 'client';
        $secret = 'secret';
        $token = 'access_token';
        $tokenDTO = TokenResponse::from([
            'access_token' => $token,
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ]);
        $action->set($tokenDTO, $client, $secret);

        $tokenFromStorage = $action->getFromStorage($client, $secret);
        $this->assertEquals($token, $tokenFromStorage);
    }

    public function test_get_from_external(): void
    {
        $action = $this->app->make(GetSetTokenAction::class);
        $this->assertInstanceOf(TokenResponse::class, $action->getFromExternal());
    }

    public function test_get_from_storage(): void
    {
        $action = $this->app->make(GetSetTokenAction::class);
        $client = 'client';
        $secret = 'secret';
        $this->assertEquals(null, $action->getFromStorage($client, $secret));

        $token = 'access_token';
        $tokenDTO = TokenResponse::from([
            'access_token' => $token,
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ]);
        $action->set($tokenDTO, $client, $secret);

        $tokenFromStorage = $action->getFromStorage($client, $secret);
        $this->assertEquals($token, $tokenFromStorage);
    }
}
