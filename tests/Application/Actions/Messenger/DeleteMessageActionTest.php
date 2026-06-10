<?php

namespace Voyanara\LaravelApiClient\Tests\Application\Actions\Messenger;

use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\SendMessageResponse;
use Voyanara\LaravelApiClient\Tests\TestCase;

class DeleteMessageActionTest extends TestCase
{
    public function test_handle(): void
    {
        $client = $this->app->make(Client::class);

        $message = $client->messenger()->sendMessage(env('DEMO_SENDER'), env('DEMO_CHAT_ID'), 'Сообщение для проверки удаления');
        $this->assertInstanceOf(SendMessageResponse::class, $message);

        $result = $client->messenger()->deleteMessage(env('DEMO_SENDER'), env('DEMO_CHAT_ID'), $message->id);

        $this->assertTrue($result);
    }
}
