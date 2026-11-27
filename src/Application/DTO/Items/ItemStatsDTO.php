<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Items;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class ItemStatsDTO extends Data
{
    /**
     * @param  int  $itemId  Идентификатор объявления
     * @param  Collection<int, ItemStatsDayDTO>  $stats  Счетчики объявления по датам
     */
    public function __construct(
        public readonly int $itemId,
        #[DataCollectionOf(ItemStatsDayDTO::class)]
        public readonly Collection $stats,
    ) {}
}
