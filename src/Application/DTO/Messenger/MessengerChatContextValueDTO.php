<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Messenger;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class MessengerChatContextValueDTO extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly array $images,
        public readonly string $priceString,
        public readonly int $statusId,
        public readonly string $title,
        public readonly string $url,
        public readonly int $user_id,
    ) {}
}
