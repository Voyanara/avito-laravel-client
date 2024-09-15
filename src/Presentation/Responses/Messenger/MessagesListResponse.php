<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\Messenger;

use Illuminate\Support\Collection;
use Voyanara\LaravelApiClient\Application\DTO\Messenger\MessengerMessageItemDTO;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class MessagesListResponse extends Data
{
    public function __construct(
        #[DataCollectionOf(MessengerMessageItemDTO::class)]
        public readonly Collection $messages,
        public readonly array $meta,
    ) {}
}
