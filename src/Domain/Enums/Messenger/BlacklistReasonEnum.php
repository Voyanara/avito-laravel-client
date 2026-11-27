<?php

namespace Voyanara\LaravelApiClient\Domain\Enums\Messenger;

/**
 * Причина, по которой блокируем пользователя (reason_id) — метод добавления пользователя в blacklist (postBlacklistV2).
 */
enum BlacklistReasonEnum: int
{
    /** Спам */
    case SPAM = 1;

    /** Мошенничество */
    case FRAUD = 2;

    /** Оскорбления и хамство */
    case INSULT = 3;

    /** Другая причина */
    case OTHER = 4;
}
