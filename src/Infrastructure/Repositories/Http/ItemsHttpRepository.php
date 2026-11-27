<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Repositories\Http;

use DateTimeInterface;
use Voyanara\LaravelApiClient\Domain\Enums\Items\AnalyticsGroupingEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\ItemStatsFieldEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\ItemStatusEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\SortOrderEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\SpendingTypeEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\StatsPeriodEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\VasEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\VasPackageEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\VasServiceEnum;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\BaseHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ApplyItemVasResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ApplyVasPackageResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ApplyVasResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\CallsStatsResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ItemInfoResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ItemsAnalyticsResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ItemsListResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ItemsStatsResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\SpendingsResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\VasPricesResponse;

class ItemsHttpRepository extends BaseHttpRepository
{
    /**
     * @param  list<ItemStatusEnum>  $statuses
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getItems(
        int $perPage = 25,
        int $page = 1,
        array $statuses = [],
        DateTimeInterface|string|null $updatedAtFrom = null,
        ?int $category = null,
    ): ItemsListResponse {
        $url = $this->apiUrl.'/core/v1/items';

        $data = self::withoutNulls([
            'per_page' => $perPage,
            'page' => $page,
            'status' => $statuses !== [] ? implode(',', self::values($statuses)) : null,
            'updatedAtFrom' => $updatedAtFrom !== null ? self::date($updatedAtFrom) : null,
            'category' => $category,
        ]);

        $response = $this->requestService->sendRequest($url, data: $data, token: $this->token);

        return ItemsListResponse::from($response + ['resources' => []]);
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getItemInfo(int $userId, int $itemId): ItemInfoResponse
    {
        $url = $this->apiUrl.'/core/v1/accounts/'.$userId.'/items/'.$itemId.'/';

        return ItemInfoResponse::from($this->requestService->sendRequest($url, token: $this->token));
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function updatePrice(int $itemId, int $price): bool
    {
        $url = $this->apiUrl.'/core/v1/items/'.$itemId.'/update_price';

        $response = $this->requestService->sendRequest($url, method: 'POST', data: ['price' => $price], token: $this->token, asJson: true);

        return (bool) ($response['result']['success'] ?? false);
    }

    /**
     * @param  array<int>  $itemIds
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getVasPrices(int $userId, array $itemIds): VasPricesResponse
    {
        $url = $this->apiUrl.'/core/v1/accounts/'.$userId.'/vas/prices';

        $response = $this->requestService->sendRequest($url, method: 'POST', data: ['itemIds' => array_values($itemIds)], token: $this->token, asJson: true);

        return VasPricesResponse::from(['items' => $response]);
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function applyItemVas(int $userId, int $itemId, VasEnum $vas): ApplyItemVasResponse
    {
        $url = $this->apiUrl.'/core/v1/accounts/'.$userId.'/items/'.$itemId.'/vas';

        $response = $this->requestService->sendRequest($url, method: 'PUT', data: ['vas_id' => $vas->value], token: $this->token, asJson: true);

        return ApplyItemVasResponse::from($response);
    }

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function applyItemVasPackage(int $userId, int $itemId, VasPackageEnum $package): ApplyVasPackageResponse
    {
        $url = $this->apiUrl.'/core/v2/accounts/'.$userId.'/items/'.$itemId.'/vas_packages';

        $response = $this->requestService->sendRequest($url, method: 'PUT', data: ['package_id' => $package->value], token: $this->token, asJson: true);

        return ApplyVasPackageResponse::from($response);
    }

    /**
     * @param  list<VasServiceEnum|string>  $services
     * @param  array<int>  $stickers
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function applyVas(int $itemId, array $services, array $stickers = []): ApplyVasResponse
    {
        $url = $this->apiUrl.'/core/v2/items/'.$itemId.'/vas/';

        $data = self::withoutNulls([
            'slugs' => self::values($services),
            'stickers' => $stickers !== [] ? array_values($stickers) : null,
        ]);

        $response = $this->requestService->sendRequest($url, method: 'PUT', data: $data, token: $this->token, asJson: true);

        return ApplyVasResponse::from([
            'operations' => array_map(static fn (array $operation): int => (int) $operation['operationId'], $response),
        ]);
    }

    /**
     * @param  array<int>  $itemIds
     * @param  list<ItemStatsFieldEnum>  $fields
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getItemsStats(
        int $userId,
        array $itemIds,
        DateTimeInterface|string $dateFrom,
        DateTimeInterface|string $dateTo,
        array $fields = [],
        ?StatsPeriodEnum $periodGrouping = null,
    ): ItemsStatsResponse {
        $url = $this->apiUrl.'/stats/v1/accounts/'.$userId.'/items';

        $data = self::withoutNulls([
            'itemIds' => array_values($itemIds),
            'dateFrom' => self::date($dateFrom),
            'dateTo' => self::date($dateTo),
            'fields' => $fields !== [] ? self::values($fields) : null,
            'periodGrouping' => $periodGrouping?->value,
        ]);

        $response = $this->requestService->sendRequest($url, method: 'POST', data: $data, token: $this->token, asJson: true);

        return ItemsStatsResponse::from([
            'items' => $response['result']['items'] ?? [],
            'errors' => $response['errors'] ?? [],
        ]);
    }

    /**
     * @param  array<string>  $metrics
     * @param  array<int>  $categoryIds
     * @param  array<int>  $employeeIds
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getItemsAnalytics(
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
        $url = $this->apiUrl.'/stats/v2/accounts/'.$userId.'/items';

        // limit and offset are required by the API, so zero values are sent as is.
        $data = self::withoutNulls([
            'dateFrom' => self::date($dateFrom),
            'dateTo' => self::date($dateTo),
            'metrics' => array_values($metrics),
            'grouping' => $grouping->value,
            'limit' => $limit,
            'offset' => $offset,
            'filter' => self::filter([
                'categoryIDs' => $categoryIds,
                'employeeIDs' => $employeeIds,
            ]),
            'sort' => $sortKey !== null ? ['key' => $sortKey, 'order' => $sortOrder->value] : null,
        ]);

        $response = $this->requestService->sendRequest($url, method: 'POST', data: $data, token: $this->token, asJson: true);

        return ItemsAnalyticsResponse::from(($response['result'] ?? []) + ['groupings' => []]);
    }

    /**
     * @param  list<SpendingTypeEnum|string>  $spendingTypes
     * @param  array<int>  $categoryIds
     * @param  array<int>  $itemIds
     * @param  array<int>  $locationIds
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getSpendings(
        int $userId,
        DateTimeInterface|string $dateFrom,
        DateTimeInterface|string $dateTo,
        array $spendingTypes = [SpendingTypeEnum::ALL],
        StatsPeriodEnum $grouping = StatsPeriodEnum::DAY,
        array $categoryIds = [],
        array $itemIds = [],
        array $locationIds = [],
    ): SpendingsResponse {
        $url = $this->apiUrl.'/stats/v2/accounts/'.$userId.'/spendings';

        $data = self::withoutNulls([
            'dateFrom' => self::date($dateFrom),
            'dateTo' => self::date($dateTo),
            'spendingTypes' => self::values($spendingTypes),
            'grouping' => $grouping->value,
            'filter' => self::filter([
                'categoryIDs' => $categoryIds,
                'itemIDs' => $itemIds,
                'locationIDs' => $locationIds,
            ]),
        ]);

        $response = $this->requestService->sendRequest($url, method: 'POST', data: $data, token: $this->token, asJson: true);

        return SpendingsResponse::from($response['result'] ?? []);
    }

    /**
     * @param  array<int>  $itemIds
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getCallsStats(
        int $userId,
        DateTimeInterface|string $dateFrom,
        DateTimeInterface|string $dateTo,
        array $itemIds = [],
    ): CallsStatsResponse {
        $url = $this->apiUrl.'/core/v1/accounts/'.$userId.'/calls/stats/';

        $data = self::withoutNulls([
            'dateFrom' => self::date($dateFrom),
            'dateTo' => self::date($dateTo),
            'itemIds' => $itemIds !== [] ? array_values($itemIds) : null,
        ]);

        $response = $this->requestService->sendRequest($url, method: 'POST', data: $data, token: $this->token, asJson: true);

        return CallsStatsResponse::from(['items' => $response['result']['items'] ?? []]);
    }

    /**
     * Avito expects dates in the YYYY-MM-DD format.
     */
    private static function date(DateTimeInterface|string $date): string
    {
        return $date instanceof DateTimeInterface ? $date->format('Y-m-d') : $date;
    }

    /**
     * Builds the optional "filter" object: empty lists are dropped, null when nothing is left.
     *
     * @param  array<string, array<int>>  $filter
     * @return array<string, list<int>>|null
     */
    private static function filter(array $filter): ?array
    {
        $filter = array_map(array_values(...), array_filter($filter, static fn (array $ids): bool => $ids !== []));

        return $filter !== [] ? $filter : null;
    }

    /**
     * Drops only null values: zeros and false are valid request values.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function withoutNulls(array $data): array
    {
        return array_filter($data, static fn (mixed $value): bool => $value !== null);
    }
}
