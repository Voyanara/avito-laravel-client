<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\UserInfo;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class UserInfoSelfResponse extends Data
{
    public function __construct(
        public readonly string $email,
        public readonly int $id,
        public readonly string $name,
        public readonly string $phone,
        public readonly string $profileUrl,
    ) {}
}
