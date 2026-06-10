<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\Messenger;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class VoiceFilesResponse extends Data
{
    /**
     * @param  array<string, string>  $voicesUrls  Карта voice_id => ссылка на файл голосового сообщения
     */
    public function __construct(
        public readonly array $voicesUrls,
    ) {}
}
