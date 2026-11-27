<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Items;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class ItemVasPricesDTO extends Data
{
    /**
     * @param  int  $itemId  Идентификатор объявления на сайте
     * @param  Collection<int, VasPriceDTO>  $vas  Стоимость услуг и пакетов услуг для объявления
     * @param  Collection<int, VasStickerDTO>|null  $stickers  Доступные для объявления значки
     */
    public function __construct(
        public readonly int $itemId,
        #[DataCollectionOf(VasPriceDTO::class)]
        public readonly Collection $vas,
        #[DataCollectionOf(VasStickerDTO::class)]
        public readonly ?Collection $stickers = null,
    ) {}
}
