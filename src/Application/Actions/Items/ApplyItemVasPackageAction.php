<?php

namespace Voyanara\LaravelApiClient\Application\Actions\Items;

use Voyanara\LaravelApiClient\Domain\Enums\Items\VasPackageEnum;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\ItemsHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ApplyVasPackageResponse;

readonly class ApplyItemVasPackageAction
{
    public function __construct(
        protected ItemsHttpRepository $repository
    ) {}

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function handle(int $userId, int $itemId, VasPackageEnum $package): ApplyVasPackageResponse
    {
        return $this->repository->applyItemVasPackage($userId, $itemId, $package);
    }
}
