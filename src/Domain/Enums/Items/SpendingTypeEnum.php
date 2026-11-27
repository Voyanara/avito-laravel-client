<?php

namespace Voyanara\LaravelApiClient\Domain\Enums\Items;

/**
 * Тип расходов для статистики расходов профиля (accountSpendings).
 */
enum SpendingTypeEnum: string
{
    /** Все расходы: сколько всего денег потрачено на объявления и прочие услуги */
    case ALL = 'all';

    /** Расходы на продвижение и услуги, которые влияют на внешний вид объявления */
    case PROMOTION = 'promotion';

    /** Расходы на размещение и целевые действия — просмотры, чаты, звонки и отклики */
    case PRESENCE = 'presence';

    /** Комиссия за заказы с Авито Доставкой и за бронирование жилья */
    case COMMISSION = 'commission';

    /** Остальные расходы: чат-боты и услуги, которые система не смогла распознать */
    case REST = 'rest';
}
