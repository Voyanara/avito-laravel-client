<?php

namespace Voyanara\LaravelApiClient\Tests\Application\Actions\Messenger;

use Voyanara\LaravelApiClient\Application\Actions\Messenger\GetChatInfoAction;
use Voyanara\LaravelApiClient\Application\Actions\Messenger\GetChatsAction;
use Voyanara\LaravelApiClient\Application\Actions\UserInfo\GetUserInfoAction;
use Voyanara\LaravelApiClient\Application\DTO\Messenger\MessengerChatItemDTO;
use Voyanara\LaravelApiClient\Presentation\Responses\UserInfo\UserInfoSelfResponse;
use Voyanara\LaravelApiClient\Tests\TestCase;

class GetChatInfoActionTest extends TestCase
{
    public function test_handle(): void
    {
        $actionChats = $this->app->make(GetChatsAction::class);
        $actionUserInfo = $this->app->make(GetUserInfoAction::class);
        $user = $actionUserInfo->handle();
        $this->assertInstanceOf(UserInfoSelfResponse::class, $user);
        $chats = $actionChats->handle($user->id, limit: 2);

        if ($chats->chats->isEmpty()) {
            $this->markTestSkipped('Live test: the Avito account has no chats.');
        }

        /**
         * @var MessengerChatItemDTO $firstChat
         */
        $firstChat = $chats->chats->first();

        $firstId = $firstChat->id;
        $chatInfoAction = $this->app->make(GetChatInfoAction::class);
        $chat = $chatInfoAction->handle($user->id, $firstId);
        $this->assertInstanceOf(MessengerChatItemDTO::class, $chat);
    }
}
