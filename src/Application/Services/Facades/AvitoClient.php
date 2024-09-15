<?php

namespace Voyanara\LaravelApiClient\Application\Services\Facades;

readonly class AvitoClient
{
    public function __construct(
        protected UserInfo $userInfo,
        protected Messenger $messenger,
    ) {}

    public function messenger(): Messenger
    {
        return $this->messenger;
    }

    public function user(): UserInfo
    {
        return $this->userInfo;
    }
}
