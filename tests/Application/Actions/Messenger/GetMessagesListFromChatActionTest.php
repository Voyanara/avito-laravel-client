<?php

namespace Voyanara\LaravelApiClient\Tests\Application\Actions\Messenger;

use Voyanara\LaravelApiClient\Application\Actions\Messenger\GetChatsAction;
use Voyanara\LaravelApiClient\Application\Actions\Messenger\GetMessagesListFromChatAction;
use Voyanara\LaravelApiClient\Application\Actions\UserInfo\GetUserInfoAction;
use Voyanara\LaravelApiClient\Application\DTO\Messenger\MessengerChatItemDTO;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\MessagesListResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\UserInfo\UserInfoSelfResponse;
use Voyanara\LaravelApiClient\Tests\TestCase;

class GetMessagesListFromChatActionTest extends TestCase
{
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

        $firstId = $firstChat->id;

        $getMessagesAction = $this->app->make(GetMessagesListFromChatAction::class);
        $this->assertInstanceOf(MessagesListResponse::class, $getMessagesAction->handle($user->id, $firstId));
    }
}
