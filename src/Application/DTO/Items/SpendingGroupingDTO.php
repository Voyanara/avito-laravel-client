<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Items;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class SpendingGroupingDTO extends Data
{
    /**
     * @param  string  $date  Дата группировки (YYYY-MM-DD)
     * @param  string  $type  Группировка: day, week, month
     * @param  Collection<int, SpendingDTO>  $spendings  Расходы
     */
    public function __construct(
        public readonly string $date,
        public readonly string $type,
        #[DataCollectionOf(SpendingDTO::class)]
        public readonly Collection $spendings,
    ) {}
}
