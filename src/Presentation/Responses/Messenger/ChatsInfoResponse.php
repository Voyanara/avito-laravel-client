<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\Messenger;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Voyanara\LaravelApiClient\Application\DTO\Messenger\MessengerChatItemDTO;

class ChatsInfoResponse extends Data
{
    public function __construct(
        #[DataCollectionOf(MessengerChatItemDTO::class)]
        public readonly Collection $chats,
        public readonly array $meta,
    ) {}
}
