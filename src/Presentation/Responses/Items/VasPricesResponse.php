<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\Items;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Voyanara\LaravelApiClient\Application\DTO\Items\ItemVasPricesDTO;

class VasPricesResponse extends Data
{
    /**
     * @param  Collection<int, ItemVasPricesDTO>  $items  Стоимость услуг продвижения и доступные значки по каждому объявлению
     */
    public function __construct(
        #[DataCollectionOf(ItemVasPricesDTO::class)]
        public readonly Collection $items,
    ) {}

    /**
     * Стоимость услуг и значки для конкретного объявления.
     */
    public function forItem(int $itemId): ?ItemVasPricesDTO
    {
        return $this->items->firstWhere('itemId', $itemId);
    }
}
