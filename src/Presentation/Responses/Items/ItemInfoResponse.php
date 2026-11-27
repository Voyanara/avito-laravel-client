<?php

namespace Voyanara\LaravelApiClient\Presentation\Responses\Items;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Voyanara\LaravelApiClient\Application\DTO\Items\ItemVasDTO;

#[MapInputName(SnakeCaseMapper::class)]
class ItemInfoResponse extends Data
{
    /**
     * @param  string  $status  Статус объявления на сайте: active, removed, old, blocked, rejected, not_found, another_user
     * @param  string|null  $autoloadItemId  Идентификатор объявления из файла автозагрузки
     * @param  string|null  $startTime  Дата создания объявления
     * @param  string|null  $finishTime  Дата завершения объявления
     * @param  string|null  $url  URL-адрес объявления
     * @param  Collection<int, ItemVasDTO>|null  $vas  Список примененных платных услуг
     */
    public function __construct(
        public readonly string $status,
        public readonly ?string $autoloadItemId = null,
        public readonly ?string $startTime = null,
        public readonly ?string $finishTime = null,
        public readonly ?string $url = null,
        #[DataCollectionOf(ItemVasDTO::class)]
        public readonly ?Collection $vas = null,
    ) {}
}
