<?php

namespace Voyanara\LaravelApiClient\Application\Actions\Items;

use DateTimeInterface;
use Voyanara\LaravelApiClient\Domain\Enums\Items\ItemStatsFieldEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\StatsPeriodEnum;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\ItemsHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ItemsStatsResponse;

readonly class GetItemsStatsAction
{
    public function __construct(
        protected ItemsHttpRepository $repository
    ) {}

    /**
     * @param  list<int>  $itemIds
     * @param  list<ItemStatsFieldEnum>  $fields
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function handle(
        int $userId,
        array $itemIds,
        DateTimeInterface|string $dateFrom,
        DateTimeInterface|string $dateTo,
        array $fields = [],
        ?StatsPeriodEnum $periodGrouping = null,
    ): ItemsStatsResponse {
        return $this->repository->getItemsStats($userId, $itemIds, $dateFrom, $dateTo, $fields, $periodGrouping);
    }
}
