<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Messenger;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class MessengerChatItemDTO extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly MessengerChatContextDTO $context,
        public readonly int $created,
        public readonly int $updated,
        public readonly array $users,
        public readonly array $lastMessage,
    ) {}
}
