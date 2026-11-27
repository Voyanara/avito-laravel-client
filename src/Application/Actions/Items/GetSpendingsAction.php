<?php

namespace Voyanara\LaravelApiClient\Application\Actions\Items;

use DateTimeInterface;
use Voyanara\LaravelApiClient\Domain\Enums\Items\SpendingTypeEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\StatsPeriodEnum;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\ItemsHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\SpendingsResponse;

readonly class GetSpendingsAction
{
    public function __construct(
        protected ItemsHttpRepository $repository
    ) {}

    /**
     * @param  list<SpendingTypeEnum|string>  $spendingTypes
     * @param  list<int>  $categoryIds
     * @param  list<int>  $itemIds
     * @param  list<int>  $locationIds
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function handle(
        int $userId,
        DateTimeInterface|string $dateFrom,
        DateTimeInterface|string $dateTo,
        array $spendingTypes = [SpendingTypeEnum::ALL],
        StatsPeriodEnum $grouping = StatsPeriodEnum::DAY,
        array $categoryIds = [],
        array $itemIds = [],
        array $locationIds = [],
    ): SpendingsResponse {
        return $this->repository->getSpendings(
            $userId,
            $dateFrom,
            $dateTo,
            $spendingTypes,
            $grouping,
            $categoryIds,
            $itemIds,
            $locationIds,
        );
    }
}
