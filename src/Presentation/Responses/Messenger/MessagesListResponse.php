<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\Messenger;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Voyanara\LaravelApiClient\Application\DTO\Messenger\MessengerMessageItemDTO;

class MessagesListResponse extends Data
{
    public function __construct(
        #[DataCollectionOf(MessengerMessageItemDTO::class)]
        public readonly Collection $messages,
        public readonly array $meta,
    ) {}
}
