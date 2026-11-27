<?php

namespace Voyanara\LaravelApiClient\Application\Actions\Items;

use DateTimeInterface;
use Voyanara\LaravelApiClient\Domain\Enums\Items\AnalyticsGroupingEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\SortOrderEnum;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\ItemsHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ItemsAnalyticsResponse;

readonly class GetItemsAnalyticsAction
{
    public function __construct(
        protected ItemsHttpRepository $repository
    ) {}

    /**
     * @param  list<string>  $metrics
     * @param  list<int>  $categoryIds
     * @param  list<int>  $employeeIds
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function handle(
        int $userId,
        DateTimeInterface|string $dateFrom,
        DateTimeInterface|string $dateTo,
        array $metrics,
        AnalyticsGroupingEnum $grouping = AnalyticsGroupingEnum::TOTALS,
        int $limit = 1000,
        int $offset = 0,
        array $categoryIds = [],
        array $employeeIds = [],
        ?string $sortKey = null,
        SortOrderEnum $sortOrder = SortOrderEnum::DESC,
    ): ItemsAnalyticsResponse {
        return $this->repository->getItemsAnalytics(
            $userId,
            $dateFrom,
            $dateTo,
            $metrics,
            $grouping,
            $limit,
            $offset,
            $categoryIds,
            $employeeIds,
            $sortKey,
            $sortOrder,
        );
    }
}
