# Plan: Store Non-Entity Timestamps in the Database Timezone

## Created
2026-10-06

## Status
completed

## Objective
Write and read every hand-formatted `Y-m-d H:i:s` timestamp (queue jobs, failed jobs, token expiry, notifications, webhook attempts) in `database.timezone` (UTC by default), the same rule `DateTimeCast` applies to entity datetimes, so a stored instant no longer depends on the PHP default timezone of whichever process wrote it.

## Related Issues
Closes #276

## Discovery Notes
- Issue #276 is a decision issue; this plan implements its recommendation, **option C** (convert through `DatabaseTimezoneConfig`).
- All dependencies (#272, #271, #274, #269, #264) are merged: the writers already read time from the injected PSR-20 clock, but format it in the clock's zone.
- `DatabaseTimezoneConfig` (marko/database) already resolves `database.timezone` (default UTC) and is autowirable (needs only `ProjectPaths`); `DatabaseTimezoneConfig::fromName()` builds one for tests. `DateTimeCast` converts with `setTimezone()` before formatting.
- Writers outside `DateTimeCast`: `DatabaseQueue` (created_at, available_at, reserved_at, reclaim and size cutoffs), `DatabaseFailedJobRepository` (failed_at write + read), `TokenManager` (expiresAt, createdAt strings), `TokenGuard` (re-parses expiresAt in the default zone), `DatabaseChannel` (notification created_at), `DatabaseNotificationRepository` (read_at), `WebhookDeliveryService` (attemptedAt).
- Entity string properties (`PersonalAccessToken::$expiresAt`, `WebhookAttempt::$attemptedAt`) stay `?string`: switching them to `DateTimeImmutable` would change the entity-derived column type (varchar to timestamp) and the public property types. The fix converts at the service boundary instead.

## Scope

### In Scope
- `DatabaseTimezoneConfig::format()` / `parse()` helpers so every writer shares one conversion
- queue-database: `DatabaseQueue`, `DatabaseFailedJobRepository`, module.php wiring, DST and mixed-process regressions, PostgreSQL (and MySQL when available) round trip
- authentication-token: `TokenManager`, `TokenGuard`, `TokenGuardFactory`
- notification `DatabaseChannel`, notification-database `DatabaseNotificationRepository`
- webhook `WebhookDeliveryService` (and declare its existing `marko/database` dependency)
- Docs: queue-database, database, authentication-token, notification(-database), webhook pages; upgrade guidance

### Out of Scope
- Pinning the MySQL session `time_zone` or moving jobs columns to `DATETIME` (documented as a follow-up note only)
- Changing entity property types
- Data migration tooling (docs give the SQL)

## Success Criteria
- [x] Queue writes and cutoffs use the database zone whatever the clock / PHP zone
- [x] failed_at written and read back in the database zone
- [x] DST fall-back and mixed-process regressions pass
- [x] Token expiry stored and compared in the database zone
- [x] Notification and webhook timestamps stored in the database zone
- [x] Docs updated with upgrade note
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | DatabaseTimezoneConfig format/parse helpers | - | completed |
| 002 | DatabaseQueue writes in the database zone | 001 | completed |
| 003 | DatabaseFailedJobRepository writes and reads in the database zone | 001, 002 | completed |
| 004 | Real-driver round trip with a non-UTC PHP default | 002, 003 | completed |
| 005 | TokenManager / TokenGuard expiry in the database zone | 001 | completed |
| 006 | Notification created_at / read_at in the database zone | 001 | completed |
| 007 | Webhook attemptedAt in the database zone | 001 | completed |
| 008 | Docs and upgrade guidance | 002, 003, 004, 005, 006, 007 | completed |

## Architecture Notes
- The clock decides *when*; `DatabaseTimezoneConfig` decides *how it is written*.
- Inject `DatabaseTimezoneConfig $databaseTimezoneConfig` as a required constructor dependency (autowired); no hidden UTC fallback. Position: immediately after `ClockInterface $clock` (after `$connection` in `DatabaseFailedJobRepository`, which has no clock), so every class and every doc example agrees.
- `DatabaseTimezoneConfig` autowires only where `ProjectPaths` is bound (Application does this). Tests that use a bare `new Container()` must bind `DatabaseTimezoneConfig::fromName('UTC')` as an instance.
- Tests use `FakeClock` with explicit zones and set/restore `date_default_timezone_set()` where a non-UTC default is needed.

## Risks & Mitigations
- Existing rows hold local wall times: documented upgrade path (drain, or CONVERT_TZ / AT TIME ZONE SQL); PR labelled `breaking`.
- Constructor signature changes break hand-built instances: all call sites updated; container autowires.
