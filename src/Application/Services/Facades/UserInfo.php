<?php

namespace Voyanara\LaravelApiClient\Application\Services\Facades;

use Voyanara\LaravelApiClient\Application\Actions\UserInfo\GetUserInfoAction;
use Voyanara\LaravelApiClient\Presentation\Responses\UserInfo\UserInfoSelfResponse;

readonly class UserInfo
{
    public function __construct(
        public GetUserInfoAction $getUserInfoAction
    ) {}

    public function self(): UserInfoSelfResponse
    {
        return $this->getUserInfoAction->handle();
    }
}
