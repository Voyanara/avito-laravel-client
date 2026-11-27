<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\Items;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Voyanara\LaravelApiClient\Application\DTO\Items\AnalyticsGroupingDTO;

class ItemsAnalyticsResponse extends Data
{
    /**
     * @param  Collection<int, AnalyticsGroupingDTO>  $groupings  Показатели в разрезе выбранной группировки
     * @param  int|null  $dataTotalCount  Общее количество сущностей (для пагинации через limit и offset)
     * @param  string|null  $timestamp  Временная метка ответа
     */
    public function __construct(
        #[DataCollectionOf(AnalyticsGroupingDTO::class)]
        public readonly Collection $groupings,
        public readonly ?int $dataTotalCount = null,
        public readonly ?string $timestamp = null,
    ) {}
}
