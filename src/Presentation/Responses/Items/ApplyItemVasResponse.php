<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\Items;

use Spatie\LaravelData\Data;
use Voyanara\LaravelApiClient\Application\DTO\Items\ItemVasDTO;

class ApplyItemVasResponse extends Data
{
    /**
     * @param  float  $amount  Сумма списания за применение услуги
     * @param  ItemVasDTO|null  $vas  Примененная услуга
     */
    public function __construct(
        public readonly float $amount,
        public readonly ?ItemVasDTO $vas = null,
    ) {}
}
