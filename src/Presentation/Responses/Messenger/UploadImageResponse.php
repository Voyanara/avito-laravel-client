<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\Messenger;

use Spatie\LaravelData\Data;

class UploadImageResponse extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly array $images,
    ) {}
}
