<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Repositories\Http;

use Voyanara\LaravelApiClient\Application\DTO\Messenger\MessengerChatItemDTO;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\BaseHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\ChatsInfoResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\MessagesListResponse;

class MessengerHttpRepository extends BaseHttpRepository
{
    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    // REVIEW: надо оставить phpdoc что это значит. Иначе твой опыт будут проходить другие разработчики
    public function getChats(int $userId, int $limit = 10, ?bool $unreadOnly = null, array $itemIds = [], array $chatTypes = [], int $offset = 0): ChatsInfoResponse
    {
        $data = array_filter([
            'limit' => $limit,
            'unread_only' => $unreadOnly !== null ? ($unreadOnly ? 'true' : 'false') : null,
            'item_ids' => $itemIds !== [] ? implode(',', $itemIds) : null,
            'chat_types' => $chatTypes !== [] ? implode(',', $chatTypes) : null,
            'offset' => $offset > 0 ? $offset : null,
        ]);

        $url = $this->apiUrl.'/messenger/v2/accounts/'.$userId.'/chats';

        // REVIEW: Исключить отсюда ДТО, т.к. вызов апп идет из инфраструктуры
        return ChatsInfoResponse::from($this->requestService->sendRequest($url, data: $data, token: $this->token));
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getMessagesListFromChat(int $userId, string $chatId, int $limit = 10, int $offset = 0): MessagesListResponse
    {
        $data = array_filter([
            'limit' => $limit,
            'offset' => $offset > 0 ? $offset : null,
        ]);
        $url = $this->apiUrl.'/messenger/v3/accounts/'.$userId.'/chats/'.$chatId.'/messages';

        return MessagesListResponse::from($this->requestService->sendRequest($url, data: $data, token: $this->token));
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getChatInfo(int $userId, string $chatId): MessengerChatItemDTO
    {
        $url = $this->apiUrl.'/messenger/v2/accounts/'.$userId.'/chats/'.$chatId;

        $response = $this->requestService->sendRequest($url, token: $this->token);

        return MessengerChatItemDTO::from($response);
    }
}
