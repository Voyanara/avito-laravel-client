<?php

namespace Voyanara\LaravelApiClient\Application\Actions\Items;

use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\ItemsHttpRepository;

readonly class UpdateItemPriceAction
{
    public function __construct(
        protected ItemsHttpRepository $repository
    ) {}

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function handle(int $itemId, int $price): bool
    {
        return $this->repository->updatePrice($itemId, $price);
    }
}
