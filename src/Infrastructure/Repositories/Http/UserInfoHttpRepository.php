<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Repositories\Http;

use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\BaseHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\UserInfo\UserInfoSelfResponse;

class UserInfoHttpRepository extends BaseHttpRepository
{
    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getUserInfo(): UserInfoSelfResponse
    {
        $url = $this->apiUrl.'/core/v1/accounts/self';
        $response = $this->requestService->sendRequest($url, token: $this->token);

        return UserInfoSelfResponse::from($response);
    }
}
