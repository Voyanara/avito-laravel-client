<?php

namespace Voyanara\LaravelApiClient\Tests\Application\Actions\Messenger;

use Voyanara\LaravelApiClient\Application\Actions\Messenger\GetChatInfoAction;
use Voyanara\LaravelApiClient\Application\Actions\Messenger\GetChatsAction;
use Voyanara\LaravelApiClient\Application\Actions\UserInfo\GetUserInfoAction;
use Voyanara\LaravelApiClient\Application\DTO\Messenger\MessengerChatItemDTO;
use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Presentation\Responses\UserInfo\UserInfoSelfResponse;
use Voyanara\LaravelApiClient\Tests\TestCase;

class ReadChatActionTest extends TestCase
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
        $chatInfoAction = $this->app->make(GetChatInfoAction::class);
        $chat = $chatInfoAction->handle($user->id, $firstId);
        $this->assertInstanceOf(MessengerChatItemDTO::class, $chat);

        $client = $this->app->make(Client::class);
        $client->messenger()->readChat(env('DEMO_SENDER'), $chat->id);

    }
}
