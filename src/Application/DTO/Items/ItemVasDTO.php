<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Items;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ItemVasDTO extends Data
{
    /**
     * @param  string  $vasId  Идентификатор услуги: vip, highlight, pushup, premium, xl
     * @param  string|null  $finishTime  Дата завершения услуги
     * @param  list<string>|null  $schedule  Даты следующих применений услуги
     */
    public function __construct(
        public readonly string $vasId,
        public readonly ?string $finishTime = null,
        public readonly ?array $schedule = null,
    ) {}
}
