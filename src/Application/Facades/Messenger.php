<?php

namespace Voyanara\LaravelApiClient\Application\Facades;

use Voyanara\LaravelApiClient\Application\Actions\Messenger\AddUserToBlacklistAction;
use Voyanara\LaravelApiClient\Application\Actions\Messenger\DeleteMessageAction;
use Voyanara\LaravelApiClient\Application\Actions\Messenger\GetChatInfoAction;
use Voyanara\LaravelApiClient\Application\Actions\Messenger\GetChatsAction;
use Voyanara\LaravelApiClient\Application\Actions\Messenger\GetMessagesListFromChatAction;
use Voyanara\LaravelApiClient\Application\Actions\Messenger\GetSubscriptionsAction;
use Voyanara\LaravelApiClient\Application\Actions\Messenger\GetVoiceFilesAction;
use Voyanara\LaravelApiClient\Application\Actions\Messenger\ReadChatAction;
use Voyanara\LaravelApiClient\Application\Actions\Messenger\SendMessageAction;
use Voyanara\LaravelApiClient\Application\Actions\Messenger\SendMessageWithImageAction;
use Voyanara\LaravelApiClient\Application\Actions\Messenger\SubscribeWebhookAction;
use Voyanara\LaravelApiClient\Application\Actions\Messenger\UnsubscribeWebhookAction;
use Voyanara\LaravelApiClient\Application\Actions\Messenger\UploadImageAction;
use Voyanara\LaravelApiClient\Application\DTO\Messenger\MessengerChatItemDTO;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\ChatsInfoResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\MessagesListResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\SendMessageResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\UploadImageResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\VoiceFilesResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\WebhookSubscriptionsResponse;

readonly class Messenger
{
    public function __construct(
        protected GetChatsAction $getChatsAction,
        protected GetChatInfoAction $chatInfoAction,
        protected SendMessageAction $sendMessageAction,
        protected ReadChatAction $readChatAction,
        protected GetMessagesListFromChatAction $getMessagesListFromChatAction,
        protected UploadImageAction $uploadImageAction,
        protected SendMessageWithImageAction $sendMessageWithImageAction,
        protected DeleteMessageAction $deleteMessageAction,
        protected GetVoiceFilesAction $getVoiceFilesAction,
        protected AddUserToBlacklistAction $addUserToBlacklistAction,
        protected SubscribeWebhookAction $subscribeWebhookAction,
        protected UnsubscribeWebhookAction $unsubscribeWebhookAction,
        protected GetSubscriptionsAction $getSubscriptionsAction
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

    /**
     * Отправка сообщения
     *
     * На данный момент можно отправить только текстовое сообщение.
     *
     * Параметры пути запроса:
     *
     * - user_id: Обязательный параметр. Целое число (int64).
     *   Идентификатор пользователя (клиента).
     *
     * - chat_id: Обязательный параметр. Строка.
     *   Идентификатор чата (клиента).
     *
     * Параметры заголовка:
     *
     * - Authorization: Обязательный параметр. Строка.
     *   Пример: Bearer ACCESS_TOKEN.
     *   Токен для авторизации.
     *
     * Authorizations:
     * - (messenger:write) AuthorizationCodeClientCredentials
     *
     * @link https://developers.avito.ru/api-catalog/messenger/documentation#operation/postSendMessage
     */
    public function sendMessage(int $userId, string $chatId, string $message, string $type = 'text'): SendMessageResponse
    {
        return $this->sendMessageAction->handle($userId, $chatId, $message, $type);
    }

    /**
     * Прочитать чат
     *
     * После успешного получения списка сообщений необходимо вызвать этот метод для того,
     * чтобы чат стал прочитанным.
     *
     * Параметры пути запроса:
     *
     * - user_id: Обязательный параметр. Целое число (int64).
     *   Идентификатор пользователя (клиента).
     *
     * - chat_id: Обязательный параметр. Строка.
     *   Идентификатор чата (клиента).
     *
     * Параметры заголовка:
     *
     * - Authorization: Обязательный параметр. Строка.
     *   Пример: Bearer ACCESS_TOKEN.
     *   Токен для авторизации.
     *
     * Authorizations:
     * - (messenger:read) AuthorizationCodeClientCredentials
     *
     * @link https://developers.avito.ru/api-catalog/messenger/documentation#operation/chatRead
     */
    public function readChat(int $userId, string $chatId): bool
    {
        return $this->readChatAction->handle($userId, $chatId);
    }

    /**
     * Загрузка изображения
     *
     * Метод используется для загрузки изображения в формате JPEG, HEIC, GIF, BMP или PNG.
     * Поддерживает загрузку только одного изображения за запрос. Для загрузки нескольких
     * изображений требуется отправлять несколько запросов.
     *
     * Параметры пути запроса:
     *
     * - user_id: Обязательный параметр. Целое число (int64).
     *   Идентификатор пользователя (клиента).
     *
     * Параметры заголовка:
     *
     * - Authorization: Обязательный параметр. Строка.
     *   Пример: Bearer ACCESS_TOKEN.
     *   Токен для авторизации.
     *
     * Параметры тела запроса:
     *
     * - uploadfile[]: Обязательный параметр. Строка (binary).
     *   Файл изображения в формате JPEG, HEIC, GIF, BMP или PNG.
     *
     * Особенности:
     * - Максимальный размер файла: 24 МБ.
     * - Максимальное разрешение: 75 мегапикселей.
     *
     * Authorizations:
     * - (messenger:write) AuthorizationCodeClientCredentials
     *
     * @param  int  $userId  Идентификатор пользователя
     * @param  string  $filePath  Путь к файлу изображения для загрузки
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://developers.avito.ru/api-catalog/messenger/documentation#operation/uploadImages
     */
    public function uploadImage(int $userId, string $filePath): UploadImageResponse
    {
        return $this->uploadImageAction->handle($userId, $filePath);
    }

    /**
     * Отправка сообщения с изображением
     *
     * Метод используется для отправки сообщения с изображением. Для этого необходимо передать
     * в запросе ID изображения, полученного после его загрузки.
     *
     * Параметры пути запроса:
     *
     * - user_id: Обязательный параметр. Целое число (int64).
     *   Идентификатор пользователя (клиента).
     *
     * - chat_id: Обязательный параметр. Строка.
     *   Идентификатор чата (клиента).
     *
     * Параметры заголовка:
     *
     * - Authorization: Обязательный параметр. Строка.
     *   Пример: Bearer ACCESS_TOKEN.
     *   Токен для авторизации.
     *
     * Параметры тела запроса:
     *
     * - image_id: Обязательный параметр. Строка.
     *   Идентификатор загруженного изображения.
     *
     * Authorizations:
     * - (messenger:write) AuthorizationCodeClientCredentials
     *
     * @param  int  $userId  Идентификатор пользователя
     * @param  string  $chatId  Идентификатор чата
     * @param  string  $imageId  Идентификатор загруженного изображения
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://developers.avito.ru/api-catalog/messenger/documentation#operation/postSendImageMessage
     */
    public function sendMessageWithImage(int $userId, string $chatId, string $imageId): SendMessageResponse
    {
        return $this->sendMessageWithImageAction->handle($userId, $chatId, $imageId);
    }

    /**
     * Удаление сообщения
     *
     * Сообщение не пропадает из истории, а меняет свой тип на deleted.
     * Удалять сообщения можно не позднее часа с момента их отправки.
     *
     * Параметры пути запроса:
     *
     * - user_id: Обязательный параметр. Целое число (int64).
     *   Идентификатор пользователя (клиента).
     *
     * - chat_id: Обязательный параметр. Строка.
     *   Идентификатор чата (клиента).
     *
     * - message_id: Обязательный параметр. Строка.
     *   Идентификатор сообщения.
     *
     * Параметры заголовка:
     *
     * - Authorization: Обязательный параметр. Строка.
     *   Пример: Bearer ACCESS_TOKEN.
     *   Токен для авторизации.
     *
     * Authorizations:
     * - (messenger:write) AuthorizationCodeClientCredentials
     *
     * @param  int  $userId  Идентификатор пользователя
     * @param  string  $chatId  Идентификатор чата
     * @param  string  $messageId  Идентификатор сообщения
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://developers.avito.ru/api-catalog/messenger/documentation#operation/deleteMessage
     */
    public function deleteMessage(int $userId, string $chatId, string $messageId): bool
    {
        return $this->deleteMessageAction->handle($userId, $chatId, $messageId);
    }

    /**
     * Получение голосовых сообщений
     *
     * Метод используется для получения ссылки на файл с голосовым сообщением по идентификатору
     * voice_id, получаемому из тела сообщения с типом voice.
     *
     * Особенности работы с голосовыми сообщениями:
     *
     * - Голосовые сообщения Авито используют кодек opus внутри .mp4 контейнера;
     * - Ссылка на голосовое сообщение доступна в течение одного часа с момента запроса.
     *   Попытка получить файл по ссылке спустя это время приведёт к ошибке.
     *   Для восстановления доступа необходимо получить новую ссылку на файл;
     * - Как и с обычными сообщениями, получение ссылки на файл доступно только для
     *   пользователей, находящихся в беседе, где голосовое сообщение было отправлено.
     *
     * Параметры запроса:
     *
     * - user_id: Обязательный параметр. Целое число (int64).
     *   Идентификатор пользователя (клиента).
     *
     * - voice_ids: Обязательный параметр. Массив строк (query).
     *   Получение файлов голосовых сообщений с указанными voice_id.
     *
     * Authorizations:
     * - (messenger:read) AuthorizationCodeClientCredentials
     *
     * @param  int  $userId  Идентификатор пользователя
     * @param  string[]  $voiceIds  Идентификаторы голосовых сообщений
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://developers.avito.ru/api-catalog/messenger/documentation#operation/getVoiceFiles
     */
    public function getVoiceFiles(int $userId, array $voiceIds): VoiceFilesResponse
    {
        return $this->getVoiceFilesAction->handle($userId, $voiceIds);
    }

    /**
     * Добавление пользователя в blacklist
     *
     * Параметры пути запроса:
     *
     * - user_id: Обязательный параметр. Целое число (int64).
     *   Идентификатор пользователя (клиента).
     *
     * Параметры тела запроса:
     *
     * - users: Массив объектов с информацией о блокируемых пользователях:
     *   - user_id: Целое число (int64). Идентификатор пользователя, которого хотим заблокировать.
     *   - context.item_id: Целое число (int64). Идентификатор объявления.
     *   - context.reason_id: Целое число. Причина, по которой блокируем пользователя:
     *     1 — спам, 2 — мошенничество, 3 — оскорбления и хамство, 4 — другая причина.
     *
     * Authorizations:
     * - (messenger:write) AuthorizationCodeClientCredentials
     *
     * @param  int  $userId  Идентификатор пользователя (владельца аккаунта)
     * @param  int  $blockedUserId  Идентификатор пользователя, которого блокируем
     * @param  int|null  $itemId  Идентификатор объявления, в контексте которого блокируем
     * @param  int|null  $reasonId  Причина блокировки: 1 — спам, 2 — мошенничество, 3 — оскорбления и хамство, 4 — другая причина
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://developers.avito.ru/api-catalog/messenger/documentation#operation/postBlacklistV2
     */
    public function addUserToBlacklist(int $userId, int $blockedUserId, ?int $itemId = null, ?int $reasonId = null): bool
    {
        return $this->addUserToBlacklistAction->handle($userId, $blockedUserId, $itemId, $reasonId);
    }

    /**
     * Включение уведомлений V3 (webhooks)
     *
     * Включение webhook-уведомлений.
     *
     * После регистрации url'а для получения веб-хуков убедитесь, что он доступен,
     * работает и возвращает статус 200 OK, соблюдая timeout 2s, например, выполнив запрос:
     *
     *   curl --connect-timeout 2 <url-вашего-вебхука> -i -d '{}'
     *
     * Параметры тела запроса:
     *
     * - url: Обязательный параметр. Строка.
     *   Url, на который будут отправляться нотификации.
     *
     * Authorizations:
     * - (messenger:read) AuthorizationCodeClientCredentials
     *
     * @param  string  $url  Url, на который будут отправляться уведомления
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://developers.avito.ru/api-catalog/messenger/documentation#operation/postWebhookV3
     */
    public function subscribeWebhook(string $url): bool
    {
        return $this->subscribeWebhookAction->handle($url);
    }

    /**
     * Отключение уведомлений (webhooks)
     *
     * Параметры тела запроса:
     *
     * - url: Обязательный параметр. Строка.
     *   Url, на который необходимо перестать слать уведомления.
     *
     * Authorizations:
     * - (messenger:read) AuthorizationCodeClientCredentials
     *
     * @param  string  $url  Url, на который необходимо перестать слать уведомления
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://developers.avito.ru/api-catalog/messenger/documentation#operation/postWebhookUnsubscribe
     */
    public function unsubscribeWebhook(string $url): bool
    {
        return $this->unsubscribeWebhookAction->handle($url);
    }

    /**
     * Получение подписок (webhooks)
     *
     * Получение списка подписок.
     *
     * Поля элемента подписки:
     *
     * - url: Строка. Url, на который отправляются уведомления.
     * - version: Строка. Версия метода, через который вебхук добавлен.
     *   Влияет на формат получаемых данных.
     *
     * Authorizations:
     * - (messenger:read) AuthorizationCodeClientCredentials
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://developers.avito.ru/api-catalog/messenger/documentation#operation/getSubscriptions
     */
    public function getSubscriptions(): WebhookSubscriptionsResponse
    {
        return $this->getSubscriptionsAction->handle();
    }
}
