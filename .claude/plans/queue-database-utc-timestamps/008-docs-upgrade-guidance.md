# Task 008: Docs and upgrade guidance

**Status**: completed
**Depends on**: 002, 003, 004, 005, 006, 007
**Retry count**: 0

## Description
Update docs pages: queue-database.md "Time and Testing" (database.timezone rule plus upgrade note: drain workers or convert with CONVERT_TZ / AT TIME ZONE; MySQL session time_zone follow-up note), database.md "Datetimes and Timezones" (list non-entity tables that follow database.timezone, document format()/parse()), clock.md if its SystemClock('UTC') guidance mentions queue tables, authentication-token.md, notification / notification-database.md, webhook.md. READMEs stay slim pointers.

## Context
- Related files: packages/docs-markdown/docs/packages/*.md, docs/DOCS-STANDARDS.md
- Positional constructor examples that break: testing.md:184 `new TokenGuard($repository, $currentRequest, $clock, $userProvider)` and queue-database.md:97 `new DatabaseQueue($connection, $envelope, $failedJobs, $queryBuilderFactory, $clock)`. Add the config argument right after `$clock`, e.g. `DatabaseTimezoneConfig::fromName('UTC')`.

## Requirements (Test Descriptions)
- [x] `it documents that queue timestamps follow database.timezone with an upgrade note`
- [x] `it lists the non-entity tables that follow database.timezone`
- [x] `it documents token, notification and webhook timestamps follow database.timezone`
- [x] `it updates positional constructor examples for the new DatabaseTimezoneConfig argument`
- [x] `it notes the visible changes: queue:failed shows failed_at in the database zone; PersonalAccessToken expiresAt/createdAt, DatabaseNotification createdAt/readAt and WebhookAttempt attemptedAt strings are database-zone wall times; ORDER BY created_at/failed_at mixes old and new rows until existing data is converted`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS

## Implementation Notes
Updated queue-database.md (rule, upgrade section with drain/convert SQL, MySQL session note, API), database.md (table list, format()/parse()), clock.md, authentication-token.md, notification.md, notification-database.md, webhook.md, testing.md (TokenGuard example). READMEs unchanged (slim pointers).
