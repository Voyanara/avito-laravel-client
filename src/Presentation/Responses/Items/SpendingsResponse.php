<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\Items;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Voyanara\LaravelApiClient\Application\DTO\Items\SpendingGroupingDTO;

class SpendingsResponse extends Data
{
    /**
     * @param  Collection<int, SpendingGroupingDTO>  $groupings  Группировки расходов
     * @param  int  $timestamp  Временная метка ответа (UTC)
     */
    public function __construct(
        #[DataCollectionOf(SpendingGroupingDTO::class)]
        public readonly Collection $groupings,
        public readonly int $timestamp,
    ) {}
}
