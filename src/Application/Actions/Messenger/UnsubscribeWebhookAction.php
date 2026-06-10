<?php

namespace Voyanara\LaravelApiClient\Application\Actions\Messenger;

use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\MessengerHttpRepository;

class UnsubscribeWebhookAction
{
    public function __construct(
        protected MessengerHttpRepository $repository
    ) {}

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function handle(string $url): bool
    {
        return $this->repository->unsubscribeWebhook($url);
    }
}
