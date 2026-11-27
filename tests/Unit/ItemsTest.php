<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Voyanara\LaravelApiClient\Application\Facades\AvitoClientFacade;
use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Application\Facades\Items;
use Voyanara\LaravelApiClient\Domain\Enums\Items\AnalyticsGroupingEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\ItemStatsFieldEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\ItemStatusEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\SortOrderEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\SpendingTypeEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\StatsPeriodEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\VasEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\VasPackageEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\VasServiceEnum;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;

beforeEach(function () {
    storeToken('valid-token');
});

function avitoItems(): Items
{
    return app(Client::class)->items();
}

it('lists items with filters', function () {
    Http::fake([
        'api.avito.ru/core/v1/items*' => Http::response([
            'meta' => ['page' => 2, 'per_page' => 50],
            'resources' => [[
                'id' => 1001,
                'title' => 'Sofa',
                'status' => 'active',
                'address' => 'Moscow',
                'price' => 15000,
                'url' => 'https://www.avito.ru/item/1001',
                'category' => ['id' => 111, 'name' => 'Furniture'],
            ], [
                'id' => 1002,
                'title' => 'Chair',
                'status' => 'old',
                'price' => null,
            ]],
        ]),
    ]);

    $items = avitoItems()->getItems(
        perPage: 50,
        page: 2,
        statuses: [ItemStatusEnum::ACTIVE, ItemStatusEnum::OLD],
        updatedAtFrom: Carbon::parse('2026-11-01 15:30:00'),
        category: 111,
    );

    expect($items->resources)->toHaveCount(2)
        ->and($items->meta)->toBe(['page' => 2, 'per_page' => 50])
        ->and($items->resources->first()->category->name)->toBe('Furniture')
        ->and($items->resources->first()->price)->toBe(15000)
        ->and($items->resources->last()->price)->toBeNull()
        ->and($items->resources->last()->category)->toBeNull();

    Http::assertSent(fn (Request $request) => $request->method() === 'GET'
        && $request->hasHeader('Authorization', 'Bearer valid-token')
        && queryOf($request) === [
            'per_page' => '50',
            'page' => '2',
            'status' => 'active,old',
            'updatedAtFrom' => '2026-11-01',
            'category' => '111',
        ]);
});

it('sends only pagination when no item filters are given', function () {
    Http::fake([
        'api.avito.ru/core/v1/items*' => Http::response(['meta' => ['page' => 1, 'per_page' => 25], 'resources' => []]),
    ]);

    expect(avitoItems()->getItems()->resources)->toBeEmpty();

    Http::assertSent(fn (Request $request) => queryOf($request) === ['per_page' => '25', 'page' => '1']);
});

it('works through the facade', function () {
    Http::fake([
        'api.avito.ru/core/v1/items*' => Http::response(['resources' => []]),
    ]);

    expect(AvitoClientFacade::items()->getItems()->meta)->toBe([]);
});

it('returns item info with applied services', function () {
    Http::fake([
        'api.avito.ru/core/v1/accounts/42/items/1001/' => Http::response([
            'autoload_item_id' => 'sofa-1',
            'start_time' => '2026-11-01T10:00:00Z',
            'finish_time' => '2026-12-01T10:00:00Z',
            'status' => 'active',
            'url' => 'https://www.avito.ru/item/1001',
            'vas' => [[
                'vas_id' => 'xl',
                'finish_time' => '2026-11-08T10:00:00Z',
                'schedule' => ['2026-11-02T10:00:00Z'],
            ]],
        ]),
    ]);

    $item = avitoItems()->getItemInfo(42, 1001);

    expect($item->status)->toBe('active')
        ->and($item->autoloadItemId)->toBe('sofa-1')
        ->and($item->startTime)->toBe('2026-11-01T10:00:00Z')
        ->and($item->vas)->toHaveCount(1)
        ->and($item->vas->first()->vasId)->toBe('xl')
        ->and($item->vas->first()->schedule)->toBe(['2026-11-02T10:00:00Z']);

    Http::assertSent(fn (Request $request) => $request->method() === 'GET'
        && $request->url() === 'https://api.avito.ru/core/v1/accounts/42/items/1001/');
});

it('returns item info without optional fields', function () {
    Http::fake([
        'api.avito.ru/core/v1/accounts/42/items/1001/' => Http::response(['status' => 'not_found', 'vas' => null]),
    ]);

    $item = avitoItems()->getItemInfo(42, 1001);

    expect($item->status)->toBe('not_found')
        ->and($item->url)->toBeNull()
        ->and($item->vas)->toBeNull();
});

it('updates the item price', function () {
    Http::fake([
        'api.avito.ru/core/v1/items/1001/update_price' => Http::response(['result' => ['success' => true]]),
    ]);

    expect(avitoItems()->updatePrice(1001, 14500))->toBeTrue();

    Http::assertSent(fn (Request $request) => $request->method() === 'POST'
        && $request->isJson()
        && $request->data() === ['price' => 14500]);
});

it('returns promotion prices and available stickers', function () {
    Http::fake([
        'api.avito.ru/core/v1/accounts/42/vas/prices' => Http::response([
            [
                'itemId' => 1234567,
                'stickers' => [['id' => 1, 'title' => 'Sticker 1', 'description' => 'Description 1']],
                'vas' => [
                    ['slug' => 'x2_7', 'price' => 200, 'priceOld' => 300],
                    ['slug' => 'brand_new_service', 'price' => 100, 'priceOld' => 150],
                ],
            ],
            ['itemId' => 7654321, 'vas' => []],
        ]),
    ]);

    $prices = avitoItems()->getVasPrices(42, [1234567, 7654321]);

    expect($prices->items)->toHaveCount(2)
        ->and($prices->forItem(1234567)->vas->first()->priceOld)->toBe(300)
        ->and($prices->forItem(1234567)->vas->first()->service())->toBe(VasServiceEnum::X2_7)
        ->and($prices->forItem(1234567)->vas->last()->service())->toBeNull()
        ->and($prices->forItem(1234567)->stickers->first()->title)->toBe('Sticker 1')
        ->and($prices->forItem(7654321)->stickers)->toBeNull()
        ->and($prices->forItem(1))->toBeNull();

    Http::assertSent(fn (Request $request) => $request->method() === 'POST'
        && $request->data() === ['itemIds' => [1234567, 7654321]]);
});

it('applies a single service with the deprecated method', function () {
    Http::fake([
        'api.avito.ru/core/v1/accounts/42/items/1001/vas' => Http::response([
            'amount' => 99.5,
            'vas' => ['vas_id' => 'highlight', 'finish_time' => '2026-11-08T10:00:00Z', 'schedule' => null],
        ]),
    ]);

    $result = avitoItems()->applyItemVas(42, 1001, VasEnum::HIGHLIGHT);

    expect($result->amount)->toBe(99.5)
        ->and($result->vas->vasId)->toBe('highlight');

    Http::assertSent(fn (Request $request) => $request->method() === 'PUT'
        && $request->isJson()
        && $request->data() === ['vas_id' => 'highlight']);
});

it('applies a service package with the deprecated method', function () {
    Http::fake([
        'api.avito.ru/core/v2/accounts/42/items/1001/vas_packages' => Http::response(['amount' => 349]),
    ]);

    expect(avitoItems()->applyItemVasPackage(42, 1001, VasPackageEnum::X10_7)->amount)->toEqual(349.0);

    Http::assertSent(fn (Request $request) => $request->method() === 'PUT'
        && $request->data() === ['package_id' => 'x10_7']);
});

it('applies promotion services with stickers', function () {
    Http::fake([
        'api.avito.ru/core/v2/items/1001/vas/' => Http::response([
            'xl' => ['operationId' => 123456790],
            'stickerpack_x2' => ['operationId' => 123456791],
            'x10_1' => ['operationId' => 123456789],
        ]),
    ]);

    $result = avitoItems()->applyVas(
        1001,
        [VasServiceEnum::XL, VasServiceEnum::STICKERPACK_X2, 'x10_1'],
        stickers: [1, 2],
    );

    expect($result->operations)->toBe([
        'xl' => 123456790,
        'stickerpack_x2' => 123456791,
        'x10_1' => 123456789,
    ])
        ->and($result->operationId(VasServiceEnum::XL))->toBe(123456790)
        ->and($result->operationId('x10_1'))->toBe(123456789)
        ->and($result->operationId(VasServiceEnum::HIGHLIGHT))->toBeNull();

    Http::assertSent(fn (Request $request) => $request->method() === 'PUT'
        && $request->url() === 'https://api.avito.ru/core/v2/items/1001/vas/'
        && $request->data() === ['slugs' => ['xl', 'stickerpack_x2', 'x10_1'], 'stickers' => [1, 2]]);
});

it('omits stickers when applying services without them', function () {
    Http::fake([
        'api.avito.ru/core/v2/items/1001/vas/' => Http::response(['highlight' => ['operationId' => 1]]),
    ]);

    avitoItems()->applyVas(1001, [VasServiceEnum::HIGHLIGHT]);

    Http::assertSent(fn (Request $request) => $request->data() === ['slugs' => ['highlight']]);
});

it('throws on promotion errors', function () {
    Http::fake([
        'api.avito.ru/core/v2/items/1001/vas/' => Http::response(['code' => 1009, 'message' => 'not enough money'], 400),
    ]);

    avitoItems()->applyVas(1001, [VasServiceEnum::XL]);
})->throws(ClientResponseException::class, 'not enough money');

it('returns stats for a list of items', function () {
    Http::fake([
        'api.avito.ru/stats/v1/accounts/42/items' => Http::response([
            'result' => [
                'items' => [[
                    'itemId' => 1001,
                    'stats' => [
                        ['date' => '2026-11-02', 'uniqViews' => 10, 'uniqContacts' => 1, 'uniqFavorites' => 0],
                        ['date' => '2026-11-09', 'uniqViews' => 7, 'uniqContacts' => 0, 'uniqFavorites' => 2],
                    ],
                ]],
            ],
        ]),
    ]);

    $stats = avitoItems()->getItemsStats(
        42,
        [1001],
        Carbon::parse('2026-11-01'),
        '2026-11-14',
        fields: [ItemStatsFieldEnum::UNIQ_VIEWS, ItemStatsFieldEnum::UNIQ_CONTACTS, ItemStatsFieldEnum::UNIQ_FAVORITES],
        periodGrouping: StatsPeriodEnum::WEEK,
    );

    expect($stats->errors)->toBe([])
        ->and($stats->forItem(1001)->stats)->toHaveCount(2)
        ->and($stats->forItem(1001)->stats->sum('uniqViews'))->toBe(17)
        ->and($stats->forItem(1001)->stats->first()->views)->toBeNull();

    Http::assertSent(fn (Request $request) => $request->method() === 'POST'
        && $request->data() === [
            'itemIds' => [1001],
            'dateFrom' => '2026-11-01',
            'dateTo' => '2026-11-14',
            'fields' => ['uniqViews', 'uniqContacts', 'uniqFavorites'],
            'periodGrouping' => 'week',
        ]);
});

it('sends only required fields for item stats', function () {
    Http::fake([
        'api.avito.ru/stats/v1/accounts/42/items' => Http::response(['result' => ['items' => []]]),
    ]);

    expect(avitoItems()->getItemsStats(42, [1001], '2026-11-01', '2026-11-07')->items)->toBeEmpty();

    Http::assertSent(fn (Request $request) => $request->data() === [
        'itemIds' => [1001],
        'dateFrom' => '2026-11-01',
        'dateTo' => '2026-11-07',
    ]);
});

it('returns profile analytics', function () {
    Http::fake([
        'api.avito.ru/stats/v2/accounts/42/items' => Http::response([
            'result' => [
                'dataTotalCount' => 2,
                'timestamp' => '2026-11-27T10:00:00Z',
                'groupings' => [
                    ['id' => 1001, 'type' => 'item', 'metrics' => [
                        ['slug' => 'views', 'value' => 120],
                        ['slug' => 'contacts', 'value' => 6],
                    ]],
                    ['id' => 1002, 'type' => 'item', 'metrics' => [
                        ['slug' => 'views', 'value' => 30],
                    ]],
                ],
            ],
        ]),
    ]);

    $analytics = avitoItems()->getItemsAnalytics(
        42,
        '2026-11-01',
        '2026-11-27',
        metrics: ['views', 'contacts'],
        grouping: AnalyticsGroupingEnum::ITEM,
        limit: 100,
        offset: 0,
        categoryIds: [111],
        sortKey: 'views',
        sortOrder: SortOrderEnum::ASC,
    );

    expect($analytics->dataTotalCount)->toBe(2)
        ->and($analytics->groupings)->toHaveCount(2)
        ->and($analytics->groupings->first()->metric('views'))->toEqual(120.0)
        ->and($analytics->groupings->last()->metric('contacts'))->toBeNull();

    Http::assertSent(fn (Request $request) => $request->method() === 'POST'
        && $request->data() === [
            'dateFrom' => '2026-11-01',
            'dateTo' => '2026-11-27',
            'metrics' => ['views', 'contacts'],
            'grouping' => 'item',
            'limit' => 100,
            'offset' => 0,
            'filter' => ['categoryIDs' => [111]],
            'sort' => ['key' => 'views', 'order' => 'asc'],
        ]);
});

it('keeps zero limit and offset in analytics and omits empty filter and sort', function () {
    Http::fake([
        'api.avito.ru/stats/v2/accounts/42/items' => Http::response(['result' => ['groupings' => []]]),
    ]);

    avitoItems()->getItemsAnalytics(42, '2026-11-01', '2026-11-27', ['views'], limit: 0);

    Http::assertSent(fn (Request $request) => $request->data() === [
        'dateFrom' => '2026-11-01',
        'dateTo' => '2026-11-27',
        'metrics' => ['views'],
        'grouping' => 'totals',
        'limit' => 0,
        'offset' => 0,
    ]);
});

it('returns profile spendings', function () {
    Http::fake([
        'api.avito.ru/stats/v2/accounts/42/spendings' => Http::response([
            'result' => [
                'timestamp' => 1795000000,
                'groupings' => [[
                    'date' => '2026-11-01',
                    'type' => 'month',
                    'spendings' => [
                        ['slug' => 'promotion', 'value' => 1500.5, 'services' => [
                            ['slug' => 'xl', 'value' => 1000],
                            ['slug' => 'highlight', 'value' => 500.5],
                        ]],
                        ['slug' => 'presence', 'value' => 300],
                    ],
                ]],
            ],
        ]),
    ]);

    $spendings = avitoItems()->getSpendings(
        42,
        Carbon::parse('2026-11-01'),
        Carbon::parse('2026-11-30'),
        spendingTypes: [SpendingTypeEnum::PROMOTION, SpendingTypeEnum::PRESENCE],
        grouping: StatsPeriodEnum::MONTH,
        itemIds: [1001],
        locationIds: [637640],
    );

    $grouping = $spendings->groupings->first();

    expect($spendings->timestamp)->toBe(1795000000)
        ->and($grouping->type)->toBe('month')
        ->and($grouping->spendings->sum('value'))->toEqual(1800.5)
        ->and($grouping->spendings->first()->services)->toHaveCount(2)
        ->and($grouping->spendings->last()->services)->toBeNull();

    Http::assertSent(fn (Request $request) => $request->method() === 'POST'
        && $request->data() === [
            'dateFrom' => '2026-11-01',
            'dateTo' => '2026-11-30',
            'spendingTypes' => ['promotion', 'presence'],
            'grouping' => 'month',
            'filter' => ['itemIDs' => [1001], 'locationIDs' => [637640]],
        ]);
});

it('requests all spendings by day by default', function () {
    Http::fake([
        'api.avito.ru/stats/v2/accounts/42/spendings' => Http::response(['result' => ['groupings' => [], 'timestamp' => 1]]),
    ]);

    avitoItems()->getSpendings(42, '2026-11-01', '2026-11-07');

    Http::assertSent(fn (Request $request) => $request->data() === [
        'dateFrom' => '2026-11-01',
        'dateTo' => '2026-11-07',
        'spendingTypes' => ['all'],
        'grouping' => 'day',
    ]);
});

it('returns calls stats', function () {
    Http::fake([
        'api.avito.ru/core/v1/accounts/42/calls/stats/' => Http::response([
            'result' => [
                'items' => [[
                    'itemId' => 1001,
                    'employeeId' => 0,
                    'days' => [
                        ['date' => '2026-11-01', 'calls' => 3, 'answered' => 2, 'new' => 1, 'newAnswered' => 1],
                        ['date' => '2026-11-02', 'calls' => 1, 'answered' => 1, 'new' => 0, 'newAnswered' => 0],
                    ],
                ]],
            ],
        ]),
    ]);

    $calls = avitoItems()->getCallsStats(42, '2026-11-01', '2026-11-02', [1001]);

    expect($calls->items->first()->itemId)->toBe(1001)
        ->and($calls->items->first()->days->sum('calls'))->toBe(4)
        ->and($calls->items->first()->days->first()->newAnswered)->toBe(1);

    Http::assertSent(fn (Request $request) => $request->method() === 'POST'
        && $request->url() === 'https://api.avito.ru/core/v1/accounts/42/calls/stats/'
        && $request->data() === ['dateFrom' => '2026-11-01', 'dateTo' => '2026-11-02', 'itemIds' => [1001]]);
});

it('requests calls stats for all items when no ids are given', function () {
    Http::fake([
        'api.avito.ru/core/v1/accounts/42/calls/stats/' => Http::response(['result' => ['items' => []]]),
    ]);

    expect(avitoItems()->getCallsStats(42, '2026-11-01', '2026-11-02')->items)->toBeEmpty();

    Http::assertSent(fn (Request $request) => $request->data() === ['dateFrom' => '2026-11-01', 'dateTo' => '2026-11-02']);
});
