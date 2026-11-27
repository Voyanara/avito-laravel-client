<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\Items;

use Spatie\LaravelData\Data;

class ApplyVasPackageResponse extends Data
{
    /**
     * @param  float  $amount  Сумма списания за применение пакета
     */
    public function __construct(
        public readonly float $amount,
    ) {}
}
