<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Items;

use Spatie\LaravelData\Data;
use Voyanara\LaravelApiClient\Domain\Enums\Items\VasServiceEnum;

class VasPriceDTO extends Data
{
    /**
     * @param  string  $slug  Идентификатор услуги или пакета услуг, см. {@see VasServiceEnum}
     * @param  int  $price  Цена в рублях с учетом скидки
     * @param  int  $priceOld  Цена в рублях до применения скидки
     */
    public function __construct(
        public readonly string $slug,
        public readonly int $price,
        public readonly int $priceOld,
    ) {}

    /**
     * Услуга из известного списка или null, если Авито вернул новый идентификатор.
     */
    public function service(): ?VasServiceEnum
    {
        return VasServiceEnum::tryFrom($this->slug);
    }
}
