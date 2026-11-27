<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\Items;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Voyanara\LaravelApiClient\Application\DTO\Items\ItemCallsStatsDTO;

class CallsStatsResponse extends Data
{
    /**
     * @param  Collection<int, ItemCallsStatsDTO>  $items  Статистика звонков по объявлениям
     */
    public function __construct(
        #[DataCollectionOf(ItemCallsStatsDTO::class)]
        public readonly Collection $items,
    ) {}
}
