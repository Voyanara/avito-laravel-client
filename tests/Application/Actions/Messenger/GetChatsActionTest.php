<?php

namespace Voyanara\LaravelApiClient\Tests\Application\Actions\Messenger;

use Illuminate\Contracts\Container\BindingResolutionException;
use Voyanara\LaravelApiClient\Application\Actions\Messenger\GetChatsAction;
use Voyanara\LaravelApiClient\Application\Actions\UserInfo\GetUserInfoAction;
use Voyanara\LaravelApiClient\Application\DTO\Messenger\MessengerChatItemDTO;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Presentation\Responses\UserInfo\UserInfoSelfResponse;
use Voyanara\LaravelApiClient\Tests\TestCase;

class GetChatsActionTest extends TestCase
{
    /**
     * @throws BindingResolutionException
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function testHandle(): void
    {
        $actionChats = $this->app->make(GetChatsAction::class);
        $actionUserInfo = $this->app->make(GetUserInfoAction::class);
        $user = $actionUserInfo->handle();
        $this->assertInstanceOf(UserInfoSelfResponse::class, $user);
        $chats = $actionChats->handle($user->id, limit: 2);

        /**
         * @var MessengerChatItemDTO $firstChat
         */
        $firstChat = $chats->chats->first();

        $firstId = $firstChat->context->value->id;

        $chatsResponse = $actionChats->handle($user->id, itemIds: [$firstId]);

        $chatIds = $chatsResponse->chats->map(fn (MessengerChatItemDTO $chat): int => $chat->context->value->id);

        $allMatch = $chatIds->every(fn ($id): bool => $id === $firstId);

        $this->assertTrue($allMatch, 'Not all chat IDs are equal to the expected ID.');

    }
}
