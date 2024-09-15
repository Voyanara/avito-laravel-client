<?php

namespace Voyanara\LaravelApiClient\Application\Services\Facades;

use Voyanara\LaravelApiClient\Application\Actions\Messenger\GetChatsAction;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\ChatsInfoResponse;

readonly class Messenger
{
    public function __construct(
        protected GetChatsAction $getChatsAction
    ) {}

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function getChats(
        int $userId,
        int $limit = 10,
        ?bool $unreadOnly = null,
        array $itemIds = [],
        array $chatTypes = [],
        int $offset = 0
    ): ChatsInfoResponse {
        return $this->getChatsAction->handle(
            $userId,
            $limit,
            $unreadOnly,
            $itemIds,
            $chatTypes,
            $offset
        );
    }
}
