<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\Items;

use BackedEnum;
use Spatie\LaravelData\Data;
use Voyanara\LaravelApiClient\Domain\Enums\Items\VasServiceEnum;

class ApplyVasResponse extends Data
{
    /**
     * @param  array<string, int>  $operations  Карта slug услуги => уникальный идентификатор операции покупки
     */
    public function __construct(
        public readonly array $operations,
    ) {}

    /**
     * Идентификатор операции покупки для услуги или null, если услуга не была применена.
     */
    public function operationId(VasServiceEnum|string $service): ?int
    {
        return $this->operations[$service instanceof BackedEnum ? $service->value : $service] ?? null;
    }
}
