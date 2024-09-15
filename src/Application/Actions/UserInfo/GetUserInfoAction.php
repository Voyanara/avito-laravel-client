<?php

namespace Voyanara\LaravelApiClient\Application\Actions\UserInfo;

use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\UserInfoHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\UserInfo\UserInfoSelfResponse;

readonly class GetUserInfoAction
{
    public function __construct(
        protected UserInfoHttpRepository $userInfoRepository
    ) {}

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function handle(): UserInfoSelfResponse
    {
        return $this->userInfoRepository->getUserInfo();
    }
}
