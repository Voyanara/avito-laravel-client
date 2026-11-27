# Changelog

All notable changes to `voyanara/avito-laravel-client` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

When releasing, rename `Unreleased` to the new version, push the tag, and the
release workflow publishes this section as the GitHub release notes.

## [Unreleased]

### Added
- Items API — `$client->items()`, all 11 methods of the [Avito Items section](https://www.avito.ru/developers/api-catalog/item/documentation): `getItems`, `getItemInfo`, `updatePrice`, `getVasPrices`, `applyVas`, `getItemsStats`, `getItemsAnalytics`, `getSpendings`, `getCallsStats`, and the methods Avito marked as deprecated, `applyItemVas` and `applyItemVasPackage`
- Backed enums for the fixed value sets of the Items API in `Domain\Enums\Items`: item statuses, promotion services and packages, statistics fields, groupings, spending types, sort order
- Date parameters of the Items API accept `DateTimeInterface` (e.g. Carbon) as well as `YYYY-MM-DD` strings
- Offline tests for every Items API method

### Changed
- `RequestService` sends `PUT` requests with `asJson: true` as JSON, the same way as `POST`

## [1.3.0] - 2026-11-27

### Added
- Offline test suite (`tests/Unit`, 33 tests) with a faked Avito API: all Messenger and account methods, token handling, error handling and both token storages
- GitHub Actions: Pint, PHPStan and tests on PHP 8.3 / 8.4 / 8.5 × Laravel 12 / 13 for every push and pull request
- Automatic GitHub releases when a tag is pushed, with notes taken from this changelog
- Composer scripts: `composer test`, `composer test:live`, `composer lint`, `composer analyse`

### Changed
- Live tests (`tests/Application`) moved to a separate `Live` suite: `composer test:live`
- Live tests are skipped instead of failing when credentials, `DEMO_*` variables or chats are missing
- `.env.testing.example` lists every variable the live tests use

### Removed
- Laravel 11 support. Laravel 11 no longer receives security fixes, and recent Composer versions refuse to install it because of unpatched security advisories. Projects on Laravel 11 can keep using `1.2.x`

### Fixed
- Live operations history test requested data older than one year, which Avito rejects

## [1.2.1] - 2026-11-27

### Fixed
- Package now works without publishing `config/avito.php` (config is merged in `register()`)
- Expired token: after a refresh, later requests reuse the new token instead of hitting 401 and refreshing on every call (affected loops, queue workers and Octane)
- Token is fetched lazily on the first API call, not when `Client` is resolved from the container
- 401/403 are checked before the `error` field, so an expired token is actually refreshed
- Image uploads use the configured `api_url`
- Default token file name typo: `no-nanme-token` → `avito-token`

### Changed
- `TokenValidException` now extends `ClientResponseException`
- Requests are logged at `debug` level instead of `info`
- Requires `spatie/laravel-data ^4.23`
- New README in English and Russian

## [1.2.0] - 2026-06-11

### Added
- Remaining Messenger API methods (full coverage of the official API): `deleteMessage`, `getVoiceFiles`, `addUserToBlacklist`, `subscribeWebhook`, `unsubscribeWebhook`, `getSubscriptions`
- PHPStan (Larastan, level 5)

### Fixed
- Safe handling of API responses with an empty body

## [1.1.0] - 2026-06-11

### Added
- Support for Laravel 12 and 13, and for PHP 8.4 and 8.5 (minimum stays at PHP 8.3)

## [1.0.10] - 2025-04-10

### Changed
- Improved token management in `RequestService`

## [1.0.9] - 2024-10-31

### Added
- Uploading images and sending messages with an image

## [1.0.8] - 2024-10-30

### Added
- Marking a chat as read (`readChat`)

## [1.0.7] - 2024-09-20

### Added
- Sending text messages (`sendMessage`)

## [1.0.0] - 2024-09-16

### Added
- First release: authorization, account info, balance, operations history, chats and messages

[Unreleased]: https://github.com/Voyanara/avito-laravel-client/compare/1.3.0...HEAD
[1.3.0]: https://github.com/Voyanara/avito-laravel-client/compare/1.2.1...1.3.0
[1.2.1]: https://github.com/Voyanara/avito-laravel-client/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/Voyanara/avito-laravel-client/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/Voyanara/avito-laravel-client/compare/1.0.10...1.1.0
[1.0.10]: https://github.com/Voyanara/avito-laravel-client/compare/1.0.9...1.0.10
[1.0.9]: https://github.com/Voyanara/avito-laravel-client/compare/1.0.8...1.0.9
[1.0.8]: https://github.com/Voyanara/avito-laravel-client/compare/1.0.7...1.0.8
[1.0.7]: https://github.com/Voyanara/avito-laravel-client/compare/1.0.6...1.0.7
[1.0.0]: https://github.com/Voyanara/avito-laravel-client/releases/tag/1.0.0
