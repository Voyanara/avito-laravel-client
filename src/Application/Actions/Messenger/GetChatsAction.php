<?php

namespace Voyanara\LaravelApiClient\Application\Actions\Messenger;

use Voyanara\LaravelApiClient\Domain\Enums\Messenger\ChatTypeEnum;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\MessengerHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\ChatsInfoResponse;

readonly class GetChatsAction
{
    public function __construct(
        protected MessengerHttpRepository $repository
    ) {}

    /**
     * @param  array<int>  $itemIds
     * @param  array<ChatTypeEnum|string>  $chatTypes
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function handle(int $userId, int $limit = 10, ?bool $unreadOnly = null, array $itemIds = [], array $chatTypes = [], int $offset = 0): ChatsInfoResponse
    {
        return $this->repository->getChats(
            $userId,
            $limit,
            $unreadOnly,
            $itemIds,
            $chatTypes,
            $offset
        );
    }
}
