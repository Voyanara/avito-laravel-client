<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Items;

use Spatie\LaravelData\Data;

class VasStickerDTO extends Data
{
    /**
     * @param  int  $id  Идентификатор значка
     * @param  string  $title  Название значка
     * @param  string  $description  Описание значка
     */
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $description,
    ) {}
}
