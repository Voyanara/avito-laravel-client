<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Items;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class SpendingDTO extends Data
{
    /**
     * @param  string  $slug  Тип расходов: promotion, presence, commission, rest
     * @param  float  $value  Сумма расходов в рублях
     * @param  Collection<int, MetricValueDTO>|null  $services  Детализация по услугам: slug услуги и сумма расходов в рублях
     */
    public function __construct(
        public readonly string $slug,
        public readonly float $value,
        #[DataCollectionOf(MetricValueDTO::class)]
        public readonly ?Collection $services = null,
    ) {}
}
