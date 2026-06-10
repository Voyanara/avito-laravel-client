<?php

namespace Voyanara\LaravelApiClient\Application\Actions\Messenger;

use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\MessengerHttpRepository;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\VoiceFilesResponse;

class GetVoiceFilesAction
{
    public function __construct(
        protected MessengerHttpRepository $repository
    ) {}

    /**
     * @param  string[]  $voiceIds
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     */
    public function handle(int $userId, array $voiceIds): VoiceFilesResponse
    {
        return $this->repository->getVoiceFiles($userId, $voiceIds);
    }
}
