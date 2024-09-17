<?php

namespace Voyanara\LaravelApiClient\Tests\Application\Actions;

use Voyanara\LaravelApiClient\Application\Actions\GetSetTokenAction;
use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Application\Facades\AvitoClientFacade;
use Voyanara\LaravelApiClient\Presentation\Responses\TokenResponse;
use Voyanara\LaravelApiClient\Tests\TestCase;

class GetSetTokenActionTest extends TestCase
{
    public function testSet(): void
    {
        $client = AvitoClientFacade::messenger();
        dd($client);
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

    public function testGetFromExternal(): void
    {
        $action = $this->app->make(GetSetTokenAction::class);
        $this->assertInstanceOf(TokenResponse::class, $action->getFromExternal());
    }

    public function testGetFromStorage(): void
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
