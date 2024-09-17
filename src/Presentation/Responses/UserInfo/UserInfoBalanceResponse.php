<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\UserInfo;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class UserInfoBalanceResponse extends Data
{
    public function __construct(
        public readonly float $bonus,
        public readonly float $real,
    ) {}
}
