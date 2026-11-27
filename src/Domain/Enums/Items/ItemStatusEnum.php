<?php

namespace Voyanara\LaravelApiClient\Domain\Enums\Items;

/**
 * Статус объявления на сайте — фильтр метода получения информации по объявлениям.
 */
enum ItemStatusEnum: string
{
    case ACTIVE = 'active';
    case REMOVED = 'removed';
    case OLD = 'old';
    case BLOCKED = 'blocked';
    case REJECTED = 'rejected';
}
