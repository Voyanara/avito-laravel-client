<?php

namespace Voyanara\LaravelApiClient\Domain\ValueObjects;

/**
 * A file sent as multipart form data.
 */
final readonly class RequestAttachVO
{
    public function __construct(
        public string $name,
        public string $content,
        public string $filename,
    ) {}
}
