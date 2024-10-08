<?php

namespace Voyanara\LaravelApiClient\Application\Facades;

readonly class Client
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
