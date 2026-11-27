<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Application\Facades\Messenger;

beforeEach(function () {
    storeToken('valid-token');
});

function messenger(): Messenger
{
    return app(Client::class)->messenger();
}

it('lists chats with filters', function () {
    Http::fake([
        'api.avito.ru/messenger/v2/accounts/42/chats*' => Http::response([
            'chats' => [avitoChat()],
            'meta' => ['has_more' => false],
        ]),
    ]);

    $chats = messenger()->getChats(42, limit: 20, unreadOnly: true, itemIds: [1, 2], chatTypes: ['u2i', 'u2u'], offset: 5);

    expect($chats->chats)->toHaveCount(1)
        ->and($chats->chats->first()->id)->toBe('chat-1')
        ->and($chats->chats->first()->context->value->title)->toBe('Sofa');

    Http::assertSent(fn (Request $request) => $request->method() === 'GET'
        && queryOf($request) === [
            'limit' => '20',
            'unread_only' => 'true',
            'item_ids' => '1,2',
            'chat_types' => 'u2i,u2u',
            'offset' => '5',
        ]);
});

it('omits empty chat filters', function () {
    Http::fake([
        'api.avito.ru/messenger/v2/accounts/42/chats*' => Http::response(['chats' => [], 'meta' => []]),
    ]);

    messenger()->getChats(42);

    Http::assertSent(fn (Request $request) => queryOf($request) === ['limit' => '10']);
});

it('returns chat info', function () {
    Http::fake([
        'api.avito.ru/messenger/v2/accounts/42/chats/chat-1' => Http::response(avitoChat()),
    ]);

    expect(messenger()->chatInfo(42, 'chat-1')->id)->toBe('chat-1');
});

it('lists messages from a chat', function () {
    Http::fake([
        'api.avito.ru/messenger/v3/accounts/42/chats/chat-1/messages*' => Http::response([
            'messages' => [[
                'id' => 'msg-1',
                'author_id' => '7',
                'created' => '1700000000',
                'direction' => 'in',
                'is_read' => true,
                'read' => 1700000050,
                'type' => 'text',
                'quote' => null,
                'content' => ['text' => 'Is it available?'],
            ]],
            'meta' => [],
        ]),
    ]);

    $messages = messenger()->messagesListFromChat(42, 'chat-1', limit: 50, offset: 10);

    expect($messages->messages->first()->content['text'])->toBe('Is it available?')
        ->and($messages->messages->first()->isRead)->toBeTrue();

    Http::assertSent(fn (Request $request) => queryOf($request) === ['limit' => '50', 'offset' => '10']);
});

it('sends a text message', function () {
    Http::fake([
        'api.avito.ru/messenger/v1/accounts/42/chats/chat-1/messages' => Http::response(avitoSentMessage()),
    ]);

    $message = messenger()->sendMessage(42, 'chat-1', 'Hi');

    expect($message->id)->toBe('msg-2')
        ->and($message->direction)->toBe('out');

    Http::assertSent(fn (Request $request) => $request->method() === 'POST'
        && $request->isJson()
        && $request->data() === ['message' => ['text' => 'Hi'], 'type' => 'text']);
});

it('marks a chat as read', function () {
    Http::fake([
        'api.avito.ru/messenger/v1/accounts/42/chats/chat-1/read' => Http::response(['ok' => true]),
    ]);

    expect(messenger()->readChat(42, 'chat-1'))->toBeTrue();

    Http::assertSent(fn (Request $request) => $request->method() === 'POST');
});

it('uploads an image to the configured API URL', function () {
    config()->set('avito.api_url', 'https://avito-proxy.test');

    Http::fake([
        'avito-proxy.test/messenger/v1/accounts/42/uploadImages' => Http::response([
            'img-123' => ['140x105' => 'https://img.avito.st/140x105/1.jpg'],
        ]),
    ]);

    $file = tempnam(sys_get_temp_dir(), 'avito').'.jpg';
    file_put_contents($file, 'fake-image');

    try {
        $upload = messenger()->uploadImage(42, $file);
    } finally {
        @unlink($file);
    }

    expect($upload->id)->toBe('img-123')
        ->and($upload->images)->toBe(['140x105' => 'https://img.avito.st/140x105/1.jpg']);

    Http::assertSent(fn (Request $request) => $request->url() === 'https://avito-proxy.test/messenger/v1/accounts/42/uploadImages'
        && $request->isMultipart());
});

it('sends an uploaded image', function () {
    Http::fake([
        'api.avito.ru/messenger/v1/accounts/42/chats/chat-1/messages/image' => Http::response(avitoSentMessage()),
    ]);

    messenger()->sendMessageWithImage(42, 'chat-1', 'img-123');

    Http::assertSent(fn (Request $request) => $request->data() === ['image_id' => 'img-123']);
});

it('deletes a message', function () {
    Http::fake([
        'api.avito.ru/messenger/v1/accounts/42/chats/chat-1/messages/msg-2' => Http::response([]),
    ]);

    expect(messenger()->deleteMessage(42, 'chat-1', 'msg-2'))->toBeTrue();

    Http::assertSent(fn (Request $request) => $request->method() === 'POST');
});

it('returns voice file links', function () {
    Http::fake([
        'api.avito.ru/messenger/v1/accounts/42/getVoiceFiles*' => Http::response([
            'voices_urls' => ['voice-1' => 'https://example.com/voice-1.mp4'],
        ]),
    ]);

    $voices = messenger()->getVoiceFiles(42, ['voice-1', 'voice-2']);

    expect($voices->voicesUrls)->toBe(['voice-1' => 'https://example.com/voice-1.mp4']);

    Http::assertSent(fn (Request $request) => queryOf($request) === ['voice_ids' => 'voice-1,voice-2']);
});

it('adds a user to the blacklist', function () {
    Http::fake([
        'api.avito.ru/messenger/v2/accounts/42/blacklist' => Http::response([]),
    ]);

    expect(messenger()->addUserToBlacklist(42, 99, itemId: 1001, reasonId: 1))->toBeTrue();

    Http::assertSent(fn (Request $request) => $request->data() === [
        'users' => [[
            'user_id' => 99,
            'context' => ['item_id' => 1001, 'reason_id' => 1],
        ]],
    ]);
});

it('subscribes to and unsubscribes from webhooks', function () {
    Http::fake([
        'api.avito.ru/messenger/v3/webhook' => Http::response(['ok' => true]),
        'api.avito.ru/messenger/v1/webhook/unsubscribe' => Http::response(['ok' => true]),
    ]);

    expect(messenger()->subscribeWebhook('https://example.com/avito/webhook'))->toBeTrue()
        ->and(messenger()->unsubscribeWebhook('https://example.com/avito/webhook'))->toBeTrue();

    Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/messenger/v3/webhook')
        && $request->data() === ['url' => 'https://example.com/avito/webhook']);
});

it('lists webhook subscriptions', function () {
    Http::fake([
        'api.avito.ru/messenger/v1/subscriptions' => Http::response([
            'subscriptions' => [['url' => 'https://example.com/avito/webhook', 'version' => '3']],
        ]),
    ]);

    $subscriptions = messenger()->getSubscriptions();

    expect($subscriptions->subscriptions)->toHaveCount(1)
        ->and($subscriptions->subscriptions->first()->version)->toBe('3');
});
