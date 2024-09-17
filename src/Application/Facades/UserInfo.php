<?php

namespace Voyanara\LaravelApiClient\Application\Facades;

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
     * Получение информации об авторизованном пользователе
     *
     * Этот метод возвращает идентификатор пользователя и его регистрационные данные.
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://developers.avito.ru/api-catalog/user/documentation#operation/getUserInfoSelf
     */
    public function self(): UserInfoSelfResponse
    {
        return $this->getUserInfoAction->handle();
    }

    /**
     * Получение истории операций пользователя
     *
     * Этот метод возвращает список операций пользователя (списания/пополнения кошелька — деньги и бонусы)
     * за определенный временной период, который задается пользователем.
     *
     * Request Body (application/json):
     *
     * - dateTimeFrom: Обязательный параметр. Строка в формате <date-time>.
     *   Время начала выборки (не далее одного года в прошлое от текущего момента).
     *
     * - dateTimeTo: Обязательный параметр. Строка в формате <date-time>.
     *   Время конца выборки (диапазон между dateTimeFrom и dateTimeTo не должен превышать одну неделю).
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://developers.avito.ru/api-catalog/user/documentation#operation/postOperationsHistory
     */
    public function getOperationsHistory(string $dateTimeFrom, string $dateTimeTo): UserInfoOperationsResponse
    {
        return $this->getUserOperationHistoryAction->handle($dateTimeFrom, $dateTimeTo);
    }

    /**
     * Получение баланса кошелька пользователя
     *
     * Этот метод возвращает сумму реальных денежных средств в кошельке, а также сумму бонусных средств.
     *
     * Параметры пути запроса:
     *
     * - user_id: Обязательный параметр. Целое число (int64).
     *   Идентификатор пользователя (клиента).
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://developers.avito.ru/api-catalog/user/documentation#operation/getUserBalance
     */
    public function getBalance(int $userId): UserInfoBalanceResponse
    {
        return $this->balanceAction->handle($userId);
    }
}
