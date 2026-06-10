<?php

namespace Voyanara\LaravelApiClient\Tests\Application\Actions\Messenger;

use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Tests\TestCase;

class UnsubscribeWebhookActionTest extends TestCase
{
    public function test_handle(): void
    {
        $client = $this->app->make(Client::class);
        $url = 'https://example.com/avito-webhook-unsubscribe-test';

        $client->messenger()->subscribeWebhook($url);

        $result = $client->messenger()->unsubscribeWebhook($url);

        $this->assertTrue($result);
    }
}
