<?php

namespace Voyanara\LaravelApiClient\Domain\Enums\Items;

/**
 * Счетчик статистики по списку объявлений (itemStatsShallow).
 */
enum ItemStatsFieldEnum: string
{
    /** Число уникальных пользователей, просмотревших объявление */
    case UNIQ_VIEWS = 'uniqViews';

    /** Число уникальных пользователей, совершивших контакты */
    case UNIQ_CONTACTS = 'uniqContacts';

    /** Число уникальных пользователей, добавивших объявление в «избранное» */
    case UNIQ_FAVORITES = 'uniqFavorites';

    /** @deprecated Используйте UNIQ_VIEWS. Общее число просмотров объявления */
    case VIEWS = 'views';

    /** @deprecated Используйте UNIQ_CONTACTS. Число контактов */
    case CONTACTS = 'contacts';

    /** @deprecated Используйте UNIQ_FAVORITES. Число добавлений объявления в «избранное» */
    case FAVORITES = 'favorites';
}
