<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Items;

use Spatie\LaravelData\Data;

class ItemDTO extends Data
{
    /**
     * @param  int  $id  Идентификатор объявления
     * @param  string  $title  Наименование объявления
     * @param  string  $status  Статус объявления на сайте: active, removed, old, blocked, rejected
     * @param  string|null  $address  Адрес объявления
     * @param  int|null  $price  Цена объявления (null означает, что цена не указана)
     * @param  string|null  $url  URL-адрес объявления
     * @param  ItemCategoryDTO|null  $category  Категория объявления
     */
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $status,
        public readonly ?string $address = null,
        public readonly ?int $price = null,
        public readonly ?string $url = null,
        public readonly ?ItemCategoryDTO $category = null,
    ) {}
}
