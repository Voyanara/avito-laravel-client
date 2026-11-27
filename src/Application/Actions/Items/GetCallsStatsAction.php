<?php

namespace Voyanara\LaravelApiClient\Application\Actions\Items;

use DateTimeInterface;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\ItemsHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\CallsStatsResponse;

readonly class GetCallsStatsAction
{
    public function __construct(
        protected ItemsHttpRepository $repository
    ) {}

    /**
     * @param  list<int>  $itemIds
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function handle(
        int $userId,
        DateTimeInterface|string $dateFrom,
        DateTimeInterface|string $dateTo,
        array $itemIds = [],
    ): CallsStatsResponse {
        return $this->repository->getCallsStats($userId, $dateFrom, $dateTo, $itemIds);
    }
}
