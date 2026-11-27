<?php

namespace Voyanara\LaravelApiClient\Application\Actions\Items;

use Voyanara\LaravelApiClient\Domain\Enums\Items\VasServiceEnum;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\ItemsHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ApplyVasResponse;

readonly class ApplyVasAction
{
    public function __construct(
        protected ItemsHttpRepository $repository
    ) {}

    /**
     * @param  list<VasServiceEnum|string>  $services
     * @param  list<int>  $stickers
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function handle(int $itemId, array $services, array $stickers = []): ApplyVasResponse
    {
        return $this->repository->applyVas($itemId, $services, $stickers);
    }
}
