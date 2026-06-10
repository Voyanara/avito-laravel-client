<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Repositories\Http;

use Voyanara\LaravelApiClient\Application\DTO\Messenger\MessengerChatItemDTO;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Domain\ValueObjects\RequestAttachVO;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\BaseHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\ChatsInfoResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\MessagesListResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\SendMessageResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\UploadImageResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\VoiceFilesResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\WebhookSubscriptionsResponse;

class MessengerHttpRepository extends BaseHttpRepository
{
    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
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

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function sendMessage(int $userId, string $chatId, string $message, string $type = 'text'): SendMessageResponse
    {
        $url = $this->apiUrl.'/messenger/v1/accounts/'.$userId.'/chats/'.$chatId.'/messages';

        $data = [
            'message' => [
                'text' => $message,
            ],
            'type' => $type,
        ];

        $response = $this->requestService->sendRequest($url, method: 'POST', data: $data, token: $this->token, asJson: true);

        return SendMessageResponse::from($response);
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function readChat(int $userId, string $chatId): true
    {
        $url = $this->apiUrl.'/messenger/v1/accounts/'.$userId.'/chats/'.$chatId.'/read';
        $this->requestService->sendRequest($url, method: 'POST', token: $this->token, asJson: true);

        return true;
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function uploadImage(int $userId, string $filePath): UploadImageResponse
    {
        $url = 'https://api.avito.ru/messenger/v1/accounts/'.$userId.'/uploadImages';
        $attach = RequestAttachVO::from([
            'name' => 'uploadfile[]',
            'content' => file_get_contents($filePath),
            'filename' => basename($filePath),
        ]);

        $response = $this->requestService->sendRequest($url, method: 'POST', token: $this->token, withAttach: true, attachData: $attach);

        return UploadImageResponse::from(['id' => array_key_first($response), 'images' => $response[array_key_first($response)]]);
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function sendMessageWithImage(int $userId, string $chatId, string $imageId): SendMessageResponse
    {
        $url = $this->apiUrl.'/messenger/v1/accounts/'.$userId.'/chats/'.$chatId.'/messages/image';

        $data = [
            'image_id' => $imageId,
        ];

        $response = $this->requestService->sendRequest($url, method: 'POST', data: $data, token: $this->token, asJson: true);

        return SendMessageResponse::from($response);
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function deleteMessage(int $userId, string $chatId, string $messageId): true
    {
        $url = $this->apiUrl.'/messenger/v1/accounts/'.$userId.'/chats/'.$chatId.'/messages/'.$messageId;
        $this->requestService->sendRequest($url, method: 'POST', token: $this->token, asJson: true);

        return true;
    }

    /**
     * @param  string[]  $voiceIds
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getVoiceFiles(int $userId, array $voiceIds): VoiceFilesResponse
    {
        $url = $this->apiUrl.'/messenger/v1/accounts/'.$userId.'/getVoiceFiles';

        $data = [
            'voice_ids' => implode(',', $voiceIds),
        ];

        return VoiceFilesResponse::from($this->requestService->sendRequest($url, data: $data, token: $this->token));
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function addUserToBlacklist(int $userId, int $blockedUserId, ?int $itemId = null, ?int $reasonId = null): true
    {
        $url = $this->apiUrl.'/messenger/v2/accounts/'.$userId.'/blacklist';

        $context = array_filter([
            'item_id' => $itemId,
            'reason_id' => $reasonId,
        ]);

        $data = [
            'users' => [
                array_filter([
                    'user_id' => $blockedUserId,
                    'context' => $context !== [] ? $context : null,
                ]),
            ],
        ];

        $this->requestService->sendRequest($url, method: 'POST', data: $data, token: $this->token, asJson: true);

        return true;
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function subscribeWebhook(string $url): bool
    {
        $response = $this->requestService->sendRequest(
            $this->apiUrl.'/messenger/v3/webhook',
            method: 'POST',
            data: ['url' => $url],
            token: $this->token,
            asJson: true
        );

        return (bool) ($response['ok'] ?? false);
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function unsubscribeWebhook(string $url): bool
    {
        $response = $this->requestService->sendRequest(
            $this->apiUrl.'/messenger/v1/webhook/unsubscribe',
            method: 'POST',
            data: ['url' => $url],
            token: $this->token,
            asJson: true
        );

        return (bool) ($response['ok'] ?? false);
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getSubscriptions(): WebhookSubscriptionsResponse
    {
        $url = $this->apiUrl.'/messenger/v1/subscriptions';

        $response = $this->requestService->sendRequest($url, method: 'POST', token: $this->token, asJson: true);

        return WebhookSubscriptionsResponse::from($response);
    }
}
