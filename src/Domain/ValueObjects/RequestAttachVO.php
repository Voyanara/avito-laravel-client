<?php

namespace Voyanara\LaravelApiClient\Domain\ValueObjects;

use Spatie\LaravelData\Data;

class RequestAttachVO extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $content,
        public readonly string $filename,
    ) {}
}
