<?php

namespace Voyanara\LaravelApiClient\Application\Actions\Messenger;

use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\MessengerHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\WebhookSubscriptionsResponse;

class GetSubscriptionsAction
{
    public function __construct(
        protected MessengerHttpRepository $repository
    ) {}

    /**
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function handle(): WebhookSubscriptionsResponse
    {
        return $this->repository->getSubscriptions();
    }
}
