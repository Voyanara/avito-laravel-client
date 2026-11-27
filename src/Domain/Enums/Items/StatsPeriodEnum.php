<?php

namespace Voyanara\LaravelApiClient\Domain\Enums\Items;

/**
 * Период группировки статистики по объявлениям (itemStatsShallow) и статистики расходов (accountSpendings).
 */
enum StatsPeriodEnum: string
{
    /** По дням */
    case DAY = 'day';

    /** По неделям — в поле date будет первый день недели */
    case WEEK = 'week';

    /** По месяцам — в поле date будет первый день месяца */
    case MONTH = 'month';
}
