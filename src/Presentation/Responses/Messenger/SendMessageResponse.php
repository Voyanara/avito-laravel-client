<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\Messenger;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]

class SendMessageResponse extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $authorId,
        public readonly int $created,
        public readonly string $direction,
        public readonly string $type,
        public readonly array $content,
    ) {}
}
