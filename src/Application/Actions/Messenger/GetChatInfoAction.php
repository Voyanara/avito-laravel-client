<?php

namespace Voyanara\LaravelApiClient\Application\Actions\Messenger;

use Voyanara\LaravelApiClient\Application\DTO\Messenger\MessengerChatItemDTO;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\MessengerHttpRepository;

class GetChatInfoAction
{
    public function __construct(
        protected MessengerHttpRepository $repository
    ) {}

    public function handle(int $userId, string $chatId): MessengerChatItemDTO
    {
        return $this->repository->getChatInfo($userId, $chatId);
    }
}
