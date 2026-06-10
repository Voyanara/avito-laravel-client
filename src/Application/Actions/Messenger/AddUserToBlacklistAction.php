<?php

namespace Voyanara\LaravelApiClient\Application\Actions\Messenger;

use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\MessengerHttpRepository;

class AddUserToBlacklistAction
{
    public function __construct(
        protected MessengerHttpRepository $repository
    ) {}

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function handle(int $userId, int $blockedUserId, ?int $itemId = null, ?int $reasonId = null): true
    {
        return $this->repository->addUserToBlacklist($userId, $blockedUserId, $itemId, $reasonId);
    }
}
