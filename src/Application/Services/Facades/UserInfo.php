<?php

namespace Voyanara\LaravelApiClient\Application\Services\Facades;

use Voyanara\LaravelApiClient\Application\Actions\UserInfo\GetUserInfoAction;
use Voyanara\LaravelApiClient\Application\Actions\UserInfo\GetUserOperationHistoryAction;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Presentation\Responses\UserInfo\UserInfoOperationsResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\UserInfo\UserInfoSelfResponse;

readonly class UserInfo
{
    public function __construct(
        public GetUserInfoAction             $getUserInfoAction,
        public GetUserOperationHistoryAction $getUserOperationHistoryAction,
    )
    {
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function self(): UserInfoSelfResponse
    {
        return $this->getUserInfoAction->handle();
    }

    /**
     * @param string $dateTimeFrom
     * @param string $dateTimeTo
     * @return UserInfoOperationsResponse
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getOperationsHistory(string $dateTimeFrom, string $dateTimeTo): UserInfoOperationsResponse
    {
        return $this->getUserOperationHistoryAction->handle($dateTimeFrom, $dateTimeTo);
    }
}
