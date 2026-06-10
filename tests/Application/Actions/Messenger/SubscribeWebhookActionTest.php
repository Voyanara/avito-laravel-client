<?php

namespace Voyanara\LaravelApiClient\Tests\Application\Actions\Messenger;

use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Tests\TestCase;

class SubscribeWebhookActionTest extends TestCase
{
    public function test_handle(): void
    {
        $client = $this->app->make(Client::class);
        $url = 'https://example.com/avito-webhook-test';

        $result = $client->messenger()->subscribeWebhook($url);

        $this->assertTrue($result);

        $client->messenger()->unsubscribeWebhook($url);
    }
}
