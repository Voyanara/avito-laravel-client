<?php

namespace Voyanara\LaravelApiClient\Application\Actions\Items;

use DateTimeInterface;
use Voyanara\LaravelApiClient\Domain\Enums\Items\ItemStatusEnum;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\ItemsHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ItemsListResponse;

readonly class GetItemsAction
{
    public function __construct(
        protected ItemsHttpRepository $repository
    ) {}

    /**
     * @param  list<ItemStatusEnum>  $statuses
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function handle(
        int $perPage = 25,
        int $page = 1,
        array $statuses = [],
        DateTimeInterface|string|null $updatedAtFrom = null,
        ?int $category = null,
    ): ItemsListResponse {
        return $this->repository->getItems($perPage, $page, $statuses, $updatedAtFrom, $category);
    }
}
