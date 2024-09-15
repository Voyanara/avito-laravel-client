<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\Messenger;

use Illuminate\Support\Collection;
use Voyanara\LaravelApiClient\Application\DTO\Messenger\MessengerChatItemDTO;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class ChatsInfoResponse extends Data
{
    public function __construct(
        #[DataCollectionOf(MessengerChatItemDTO::class)]
        public readonly Collection $chats,
        public readonly array $meta,
    ) {}
}
