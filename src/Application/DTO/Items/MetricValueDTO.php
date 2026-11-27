<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Items;

use Spatie\LaravelData\Data;

class MetricValueDTO extends Data
{
    /**
     * @param  string  $slug  Идентификатор показателя или услуги
     * @param  float  $value  Значение
     */
    public function __construct(
        public readonly string $slug,
        public readonly float $value,
    ) {}
}
