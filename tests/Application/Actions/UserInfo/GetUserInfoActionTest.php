<?php

namespace Voyanara\LaravelApiClient\Tests\Application\Actions\UserInfo;

use Illuminate\Contracts\Container\BindingResolutionException;
use Voyanara\LaravelApiClient\Application\Actions\UserInfo\GetUserInfoAction;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Presentation\Responses\UserInfo\UserInfoSelfResponse;
use Voyanara\LaravelApiClient\Tests\TestCase;

class GetUserInfoActionTest extends TestCase
{
    /**
     * @throws ClientResponseException
     * @throws BindingResolutionException
     * @throws TokenValidException
     */
    public function testHandle(): void
    {
        $client = $this->app->make(GetUserInfoAction::class);
        $this->assertInstanceOf(UserInfoSelfResponse::class, $client->handle());
    }
}
