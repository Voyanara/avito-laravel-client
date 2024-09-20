<?php

namespace Voyanara\LaravelApiClient\Application\Actions\Messenger;

use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\MessengerHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\SendMessageResponse;

class SendMessageAction
{
    public function __construct(
        protected MessengerHttpRepository $repository
    ) {}

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function handle(int $userId, string $chatId, string $message, string $type = 'text'): SendMessageResponse
    {
        return $this->repository->sendMessage($userId, $chatId, $message, $type);
    }
}
