<?php

namespace Voyanara\LaravelApiClient\Application\Facades;

use Voyanara\LaravelApiClient\Application\Actions\Messenger\GetChatInfoAction;
use Voyanara\LaravelApiClient\Application\Actions\Messenger\GetChatsAction;
use Voyanara\LaravelApiClient\Application\Actions\Messenger\GetMessagesListFromChatAction;
use Voyanara\LaravelApiClient\Application\DTO\Messenger\MessengerChatItemDTO;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\ChatsInfoResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\MessagesListResponse;

readonly class Messenger
{
    public function __construct(
        protected GetChatsAction $getChatsAction,
        protected GetChatInfoAction $chatInfoAction,
        protected GetMessagesListFromChatAction $getMessagesListFromChatAction
    ) {}

    /**
     * Получение информации по чатам
     *
     * Этот метод возвращает список чатов.
     *
     *  Описание параметров запроса:
     *
     *  - item_ids: Массив целых чисел (int64). Пример: `item_ids=12345,6789`.
     *    Позволяет получить чаты только для указанных ID объявлений.
     *
     *  - unread_only: Логический параметр. По умолчанию: `false`. Пример: `unread_only=true`.
     *    Если установлено в `true`, возвращаются только непрочитанные чаты.
     *
     *  - chat_types: Массив строк. По умолчанию: "u2i". Пример: `chat_types=u2i,u2u`.
     *    Позволяет фильтровать чаты по типу:
     *      - "u2i": чаты, связанные с объявлениями;
     *      - "u2u": чаты между пользователями.
     *
     *  - limit: Целое число (int32) от 1 до 100. По умолчанию: `100`. Пример: `limit=50`.
     *    Ограничение на количество запрашиваемых чатов.
     *
     *  - offset: Целое число (int32). По умолчанию: `0`. Пример: `offset=50`.
     *    Смещение для запроса списка чатов.
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://developers.avito.ru/api-catalog/messenger/documentation#operation/getChatsV2
     */
    public function getChats(
        int $userId,
        int $limit = 10,
        ?bool $unreadOnly = null,
        array $itemIds = [],
        array $chatTypes = [],
        int $offset = 0
    ): ChatsInfoResponse {
        return $this->getChatsAction->handle(
            $userId,
            $limit,
            $unreadOnly,
            $itemIds,
            $chatTypes,
            $offset
        );
    }

    /**
     * Получение списка сообщений V3
     *
     * Этот метод позволяет получить список сообщений, не помечая чат как прочитанный.
     *
     * Описание параметров запроса:
     *
     * - limit: Целое число (int32) от 1 до 100. По умолчанию: `100`. Пример: `limit=50`.
     *   Количество сообщений или чатов для запроса.
     *
     * - offset: Целое число (int32). По умолчанию: `0`. Пример: `offset=50`.
     *   Сдвиг для запроса сообщений или чатов.
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://developers.avito.ru/api-catalog/messenger/documentation#operation/getMessagesV3
     */
    public function messagesListFromChat(int $userId, string $chatId, int $limit = 10, int $offset = 0): MessagesListResponse
    {
        return $this->getMessagesListFromChatAction->handle($userId, $chatId, $limit, $offset);
    }

    /**
     * Получение информации по чату
     *
     * Этот метод возвращает данные чата и последнее сообщение в нем.
     *
     * Параметры пути запроса:
     *
     * - user_id: Обязательный параметр. Целое число (int64).
     *   Идентификатор пользователя (клиента).
     *
     * - chat_id: Обязательный параметр. Строка.
     *   Идентификатор чата (клиента).
     *
     *
     * @link https://developers.avito.ru/api-catalog/messenger/documentation#operation/getChatByIdV2
     */
    public function chatInfo(int $userId, string $chatId): MessengerChatItemDTO
    {
        return $this->chatInfoAction->handle($userId, $chatId);
    }
}
