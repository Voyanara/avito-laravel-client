<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Messenger;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class MessengerChatContextDTO extends Data
{
    public function __construct(
        public readonly string $type,
        public readonly MessengerChatContextValueDTO $value,
    ) {}
}
