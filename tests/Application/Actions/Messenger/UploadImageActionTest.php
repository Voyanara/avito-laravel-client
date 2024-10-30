<?php

namespace Voyanara\LaravelApiClient\Tests\Application\Actions\Messenger;

use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\SendMessageResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\UploadImageResponse;
use Voyanara\LaravelApiClient\Tests\TestCase;

class UploadImageActionTest extends TestCase
{
    public function testHandle(): void
    {
        $client = $this->app->make(Client::class);
        $filePath = __DIR__.'/Screenshot_1.jpg';

        $upload = $client->messenger()->uploadImage(env('DEMO_SENDER'), $filePath);

        $this->assertInstanceOf(UploadImageResponse::class, $upload);

        $response = $client->messenger()->sendMessageWithImage(env('DEMO_SENDER'), env('DEMO_CHAT_ID'), $upload->id);

        $this->assertInstanceOf(SendMessageResponse::class, $response);
    }
}
