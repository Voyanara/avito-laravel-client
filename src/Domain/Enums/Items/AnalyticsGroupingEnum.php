<?php

namespace Voyanara\LaravelApiClient\Domain\Enums\Items;

/**
 * Группировка статистических показателей по профилю (itemAnalytics).
 */
enum AnalyticsGroupingEnum: string
{
    /** По общему значению показателя за период, без детализации */
    case TOTALS = 'totals';

    /** По объявлениям */
    case ITEM = 'item';

    /** По дням */
    case DAY = 'day';

    /** По неделям */
    case WEEK = 'week';

    /** По месяцам */
    case MONTH = 'month';
}
