<?php

namespace Voyanara\LaravelApiClient\Application\Services\Facades;

readonly class AvitoClient
{
    // REVIEW: Убрать из фасадов на уровень выше. Аналогично и другие файлы этой папки
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
