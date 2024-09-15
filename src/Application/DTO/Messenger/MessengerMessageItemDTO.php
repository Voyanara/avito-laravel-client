<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Messenger;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class MessengerMessageItemDTO extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $authorId,

        public readonly string $created,
        public readonly string $direction,
        public readonly bool $isRead,
        public readonly ?int $read,
        public readonly string $type,
        public readonly ?array $quote,
        public readonly array $content,
    ) {}
}
