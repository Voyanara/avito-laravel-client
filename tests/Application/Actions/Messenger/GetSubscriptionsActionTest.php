<?php

namespace Voyanara\LaravelApiClient\Tests\Application\Actions\Messenger;

use Voyanara\LaravelApiClient\Application\DTO\Messenger\WebhookSubscriptionItemDTO;
use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\WebhookSubscriptionsResponse;
use Voyanara\LaravelApiClient\Tests\TestCase;

class GetSubscriptionsActionTest extends TestCase
{
    public function test_handle(): void
    {
        $client = $this->app->make(Client::class);
        $url = 'https://example.com/avito-webhook-subscriptions-test';

        $client->messenger()->subscribeWebhook($url);

        $response = $client->messenger()->getSubscriptions();

        $this->assertInstanceOf(WebhookSubscriptionsResponse::class, $response);

        $urls = $response->subscriptions->map(fn (WebhookSubscriptionItemDTO $subscription): string => $subscription->url);

        $this->assertContains($url, $urls);

        $client->messenger()->unsubscribeWebhook($url);
    }
}
