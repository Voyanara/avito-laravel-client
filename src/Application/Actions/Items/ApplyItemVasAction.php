<?php

namespace Voyanara\LaravelApiClient\Application\Actions\Items;

use Voyanara\LaravelApiClient\Domain\Enums\Items\VasEnum;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\ItemsHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ApplyItemVasResponse;

readonly class ApplyItemVasAction
{
    public function __construct(
        protected ItemsHttpRepository $repository
    ) {}

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function handle(int $userId, int $itemId, VasEnum $vas): ApplyItemVasResponse
    {
        return $this->repository->applyItemVas($userId, $itemId, $vas);
    }
}
