<?php

namespace Voyanara\LaravelApiClient\Application\Actions\UserInfo;

use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\UserInfoHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\UserInfo\UserInfoOperationsResponse;

readonly class GetUserOperationHistoryAction
{
    public function __construct(
        protected UserInfoHttpRepository $userInfoRepository
    ) {}

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function handle(string $dateTimeFrom, string $dateTimeTo): UserInfoOperationsResponse
    {
        return $this->userInfoRepository->getUserOperationHistory($dateTimeFrom, $dateTimeTo);
    }
}
