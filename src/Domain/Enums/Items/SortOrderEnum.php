<?php

namespace Voyanara\LaravelApiClient\Domain\Enums\Items;

/**
 * Порядок сортировки статистических показателей по профилю (itemAnalytics).
 */
enum SortOrderEnum: string
{
    case ASC = 'asc';
    case DESC = 'desc';
}
