<?php

namespace Voyanara\LaravelApiClient\Tests\Application\Actions\Messenger;

use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\SendMessageResponse;
use Voyanara\LaravelApiClient\Tests\TestCase;

class SendMessageActionTest extends TestCase
{
    public function testHandle(): void
    {
        $client = $this->app->make(Client::class);
        $response = $client->messenger()->sendMessage(env('DEMO_SENDER'), env('DEMO_CHAT_ID'), 'Добрый день это ответное авто сообщение');
        $this->assertInstanceOf(SendMessageResponse::class, $response);
    }
}
