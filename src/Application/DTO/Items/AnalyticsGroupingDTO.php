<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Items;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class AnalyticsGroupingDTO extends Data
{
    /**
     * @param  string  $type  Тип группировки: totals, item, day, week, month
     * @param  Collection<int, MetricValueDTO>  $metrics  Значения показателей. Недоступная пользователю метрика в ответ не приходит
     * @param  int|null  $id  Идентификатор группировки (например, идентификатор объявления для группировки item)
     */
    public function __construct(
        public readonly string $type,
        #[DataCollectionOf(MetricValueDTO::class)]
        public readonly Collection $metrics,
        public readonly ?int $id = null,
    ) {}

    /**
     * Значение показателя по его идентификатору или null, если показатель не пришёл в ответе.
     */
    public function metric(string $slug): ?float
    {
        return $this->metrics->firstWhere('slug', $slug)?->value;
    }
}
