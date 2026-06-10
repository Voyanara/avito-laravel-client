<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Messenger;

use Spatie\LaravelData\Data;

class WebhookSubscriptionItemDTO extends Data
{
    public function __construct(
        public readonly string $url,
        public readonly string $version,
    ) {}
}
