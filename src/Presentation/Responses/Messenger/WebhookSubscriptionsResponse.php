<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\Messenger;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Voyanara\LaravelApiClient\Application\DTO\Messenger\WebhookSubscriptionItemDTO;

class WebhookSubscriptionsResponse extends Data
{
    public function __construct(
        #[DataCollectionOf(WebhookSubscriptionItemDTO::class)]
        public readonly Collection $subscriptions,
    ) {}
}
