<?php

namespace Voyanara\LaravelApiClient\Application\Services\Facades;

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
     * @throws ClientResponseException
     * @throws TokenValidException
     *                             Получение информации по чатам
     *                             Возвращает список чатов
     *                             https://developers.avito.ru/api-catalog/messenger/documentation#operation/getChatsV2
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
     * @throws ClientResponseException
     * @throws TokenValidException
     *                             Получение списка сообщений V3
     *                             Получение списка сообщений. Не помечает чат прочитанным.
     *                             https://developers.avito.ru/api-catalog/messenger/documentation#operation/getMessagesV3
     */
    public function messagesListFromChat(int $userId, string $chatId, int $limit = 10, int $offset = 0): MessagesListResponse
    {
        return $this->getMessagesListFromChatAction->handle($userId, $chatId, $limit, $offset);
    }

    /**
     * @return MessengerChatItemDTO
     *                              Получение информации по чату
     *                              Возвращает данные чата и последнее сообщение в нем
     */
    public function chatInfo(int $userId, string $chatId): MessengerChatItemDTO
    {
        return $this->chatInfoAction->handle($userId, $chatId);
    }
}
