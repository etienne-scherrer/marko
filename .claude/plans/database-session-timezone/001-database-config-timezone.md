# Task 001: DatabaseConfig carries the database timezone

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Give `DatabaseConfig` the zone from the `timezone` key of `config/database.php` (default `UTC`), validated the same way as `DatabaseTimezoneConfig`, so the connections built from it can pin the session zone.

## Context
- Related files: packages/database/src/Config/DatabaseConfig.php, packages/database/src/Config/DatabaseTimezoneConfig.php, packages/database/tests/Config/
- Patterns to follow: `DatabaseTimezoneConfig::resolveTimezone()` (reuse it, do not duplicate validation). It is currently `private static`; change it to `public static` (keep the `mixed` parameter, so a non-string config value raises `ConfigurationException` rather than a TypeError).

## Interface Contract (tasks 002/003 build against this)
- `public string $timezone`: the canonical `DateTimeZone::getName()` of the resolved zone (e.g. `utc` becomes `UTC`, `europe/paris` becomes `Europe/Paris`, `+05:30` stays `+05:30`, `EST` stays `EST`).
- `public function fixedTimezoneOffset(): ?string` returns `'+HH:MM'` / `'-HH:MM'` (PHP `format('P')` of the zone's current offset) when the zone is fixed, otherwise null. A zone is fixed when its name is `UTC` (returns `'+00:00'`) or `DateTimeZone::getLocation() === false` (offsets and abbreviations). Note that PHP returns a location for `UTC`, so it must be special-cased.
- Both `__construct()` and `fromArray()` must set `timezone`. `fromArray()` sets properties through an explicit `$props` list on a readonly class, so a missing entry leaves the property uninitialized.

## Requirements (Test Descriptions)
- [x] `it defaults the timezone to UTC when config/database.php has no timezone key`
- [x] `it reads the timezone from config/database.php`
- [x] `it reads the timezone in fromArray()`
- [x] `it rejects an invalid timezone with ConfigurationException`
- [x] `it reports a fixed offset for UTC, numeric offsets and abbreviations`
- [x] `it reports no fixed offset for a region zone`
- [x] `it stores the canonical zone name`
- [x] `it defaults the timezone to UTC in fromArray() when the key is absent`
- [x] `it rejects an invalid timezone in fromArray() with ConfigurationException`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
`DatabaseConfig::$timezone` is a `DateTimeZone` (not a string) so callers get the validated, canonical zone and its name through `getName()`. The canonical-name and fromArray() default/invalid requirements are covered by the tests listed above.
