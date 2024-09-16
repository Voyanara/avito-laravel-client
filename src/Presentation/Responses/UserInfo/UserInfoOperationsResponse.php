<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\UserInfo;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Voyanara\LaravelApiClient\Application\DTO\UserInfo\UserOperationItemDTO;

#[MapInputName(SnakeCaseMapper::class)]
class UserInfoOperationsResponse extends Data
{
    public function __construct(
        #[DataCollectionOf(UserOperationItemDTO::class)]
        public readonly Collection $operations,
    ) {}
}
