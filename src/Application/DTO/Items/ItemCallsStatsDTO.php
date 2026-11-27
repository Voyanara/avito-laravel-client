<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Items;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class ItemCallsStatsDTO extends Data
{
    /**
     * @param  int  $itemId  Идентификатор объявления (0 — звонок не атрибутирован до объявления)
     * @param  int  $employeeId  Идентификатор сотрудника в иерархии аккаунтов (0 — звонок не атрибутирован до сотрудника)
     * @param  Collection<int, CallsStatsDayDTO>|null  $days  Статистика звонков объявления по дням
     */
    public function __construct(
        public readonly int $itemId,
        public readonly int $employeeId,
        #[DataCollectionOf(CallsStatsDayDTO::class)]
        public readonly ?Collection $days = null,
    ) {}
}
