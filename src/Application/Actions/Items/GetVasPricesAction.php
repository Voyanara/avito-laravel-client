<?php

namespace Voyanara\LaravelApiClient\Application\Actions\Items;

use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\ItemsHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\VasPricesResponse;

readonly class GetVasPricesAction
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
    public function handle(int $userId, array $itemIds): VasPricesResponse
    {
        return $this->repository->getVasPrices($userId, $itemIds);
    }
}
