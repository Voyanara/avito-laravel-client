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
        protected GetUserInfoAction $getUserInfoAction,
        protected GetUserOperationHistoryAction $getUserOperationHistoryAction,
        protected GetUserBalanceAction $balanceAction,
    ) {}

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     *                             Получение информации об авторизованном пользователе
     *                             Возвращает идентификатор пользователя и его регистрационные данные
     *                             https://developers.avito.ru/api-catalog/user/documentation#operation/getUserInfoSelf
     */
    public function self(): UserInfoSelfResponse
    {
        return $this->getUserInfoAction->handle();
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     *                             Получение истории операций пользователя
     *                             Возвращает список операций пользователя (списания/пополнение кошелька - деньги и бонусы)
     *                             за определенный временной период (задается пользователем)
     *                             https://developers.avito.ru/api-catalog/user/documentation#operation/postOperationsHistory
     */
    public function getOperationsHistory(string $dateTimeFrom, string $dateTimeTo): UserInfoOperationsResponse
    {
        return $this->getUserOperationHistoryAction->handle($dateTimeFrom, $dateTimeTo);
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     *                             Получение баланса кошелька пользователя
     *                             Возвращает сумму реальных денежных средств в кошельке, а также сумму бонусных средств
     *                             https://developers.avito.ru/api-catalog/user/documentation#operation/getUserBalance
     */
    public function getBalance(int $userId): UserInfoBalanceResponse
    {
        return $this->balanceAction->handle($userId);
    }
}
