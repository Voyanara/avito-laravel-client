<?php

namespace Voyanara\LaravelApiClient\Application\Services\Facades;

use Voyanara\LaravelApiClient\Application\Actions\UserInfo\GetUserBalanceAction;
use Voyanara\LaravelApiClient\Application\Actions\UserInfo\GetUserInfoAction;
use Voyanara\LaravelApiClient\Application\Actions\UserInfo\GetUserOperationHistoryAction;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Presentation\Responses\UserInfo\UserInfoBalanceResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\UserInfo\UserInfoOperationsResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\UserInfo\UserInfoSelfResponse;

readonly class UserInfo
{
    public function __construct(
        public GetUserInfoAction $getUserInfoAction,
        public GetUserOperationHistoryAction $getUserOperationHistoryAction,
        public GetUserBalanceAction $balanceAction,
    ) {}

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function self(): UserInfoSelfResponse
    {
        return $this->getUserInfoAction->handle();
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getOperationsHistory(string $dateTimeFrom, string $dateTimeTo): UserInfoOperationsResponse
    {
        return $this->getUserOperationHistoryAction->handle($dateTimeFrom, $dateTimeTo);
    }


    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getBalance(int $userId): UserInfoBalanceResponse
    {
      return  $this->balanceAction->handle($userId);
    }
}
