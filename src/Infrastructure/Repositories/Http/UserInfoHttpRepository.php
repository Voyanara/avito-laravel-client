<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Repositories\Http;

use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\BaseHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\UserInfo\UserInfoOperationsResponse;
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

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getUserOperationHistory(string $dateFrom, string $dateTo): UserInfoOperationsResponse
    {
        $url = $this->apiUrl.'/core/v1/accounts/operations_history';
        $data = [
            'dateTimeFrom' => $dateFrom,
            'dateTimeTo' => $dateTo,
        ];
        $response = $this->requestService->sendRequest($url, method: 'POST', data: $data, token: $this->token, asJson: true);

        $operations = $response['result'] ?? [];

        return UserInfoOperationsResponse::from($operations);
    }
}
