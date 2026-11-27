<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\Items;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Voyanara\LaravelApiClient\Application\DTO\Items\ItemDTO;

class ItemsListResponse extends Data
{
    /**
     * @param  Collection<int, ItemDTO>  $resources  Объявления
     * @param  array{page?: int, per_page?: int}  $meta  Номер страницы и количество записей на странице
     */
    public function __construct(
        #[DataCollectionOf(ItemDTO::class)]
        public readonly Collection $resources,
        public readonly array $meta = [],
    ) {}
}
