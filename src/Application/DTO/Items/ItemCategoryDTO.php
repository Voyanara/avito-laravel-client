<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Items;

use Spatie\LaravelData\Data;

class ItemCategoryDTO extends Data
{
    /**
     * @param  int  $id  Идентификатор категории
     * @param  string  $name  Наименование категории
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {}
}
