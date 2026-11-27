<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\Items;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Voyanara\LaravelApiClient\Application\DTO\Items\ItemStatsDTO;

class ItemsStatsResponse extends Data
{
    /**
     * @param  Collection<int, ItemStatsDTO>  $items  Статистические счетчики по объявлениям
     * @param  array<string, mixed>  $errors  Ошибки, которые Авито вернул вместе с успешным ответом
     */
    public function __construct(
        #[DataCollectionOf(ItemStatsDTO::class)]
        public readonly Collection $items,
        public readonly array $errors = [],
    ) {}

    /**
     * Счетчики конкретного объявления.
     */
    public function forItem(int $itemId): ?ItemStatsDTO
    {
        return $this->items->firstWhere('itemId', $itemId);
    }
}
