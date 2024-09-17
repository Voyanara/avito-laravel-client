<?php

namespace Voyanara\LaravelApiClient\Tests\Application\Actions\UserInfo;

use Voyanara\LaravelApiClient\Application\Actions\UserInfo\GetUserBalanceAction;
use Voyanara\LaravelApiClient\Application\Actions\UserInfo\GetUserInfoAction;
use Voyanara\LaravelApiClient\Presentation\Responses\UserInfo\UserInfoBalanceResponse;
use Voyanara\LaravelApiClient\Tests\TestCase;

class GetUserBalanceActionTest extends TestCase
{
    public function testHandle(): void
    {
        $balanceAction = $this->app->make(GetUserBalanceAction::class);
        $client = $this->app->make(GetUserInfoAction::class);
        $id = $client->handle()->id;
        $this->assertInstanceOf(UserInfoBalanceResponse::class, $balanceAction->handle($id));
    }
}
