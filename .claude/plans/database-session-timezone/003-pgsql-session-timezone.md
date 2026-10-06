# Task 003: PostgreSQL connection pins the session time zone

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`PgSqlConnection::connect()` runs `SET TIME ZONE 'UTC'` / `SET TIME ZONE '<name>'` / `SET TIME ZONE INTERVAL '<offset>' HOUR TO MINUTE` after `SET NAMES`. A rejected zone becomes a `ConnectionException` naming the zone.

## Context
- Related files: packages/database-pgsql/src/Connection/PgSqlConnection.php, packages/database-pgsql/src/Exceptions/ConnectionException.php, packages/database-pgsql/tests/Connection/*, packages/database-pgsql/tests/Fixtures/Retry/ScriptedPdo.php
- SQLite fakes that skip `SET NAMES` must skip the time zone statement too.
- Statement choice: `$config->timezone === 'UTC'` gives `SET TIME ZONE 'UTC'`; a non-null `fixedTimezoneOffset()` gives `SET TIME ZONE INTERVAL '<offset>' HOUR TO MINUTE`; otherwise `SET TIME ZONE '<name>'` (see the task 001 contract).
- Today `connect()` assigns `$this->pdo` before `SET NAMES` runs. If a session statement fails, the PDO stays set, the next `connect()` returns early, and the session silently runs in the server zone. Build the PDO in a local variable, run `SET NAMES` and `SET TIME ZONE`, and only then assign `$this->pdo`.
- Run `SET TIME ZONE` in its own try/catch so a rejection (SQLSTATE 22023) maps to the new `ConnectionException::unknownTimezone(string $timezone, PDOException $previous)` instead of `connectionFailed`.

## Requirements (Test Descriptions)
- [x] `it sets the session time zone to UTC on connect when the database timezone is UTC`
- [x] `it sets the session time zone to the named zone on connect`
- [x] `it sets a fixed offset as an ISO interval so PostgreSQL does not invert its sign`
- [x] `it sets the session time zone again after a reconnect`
- [x] `it throws ConnectionException naming the zone when the server rejects the time zone`
- [x] `it stays disconnected after a failed session setup so the next connect retries it`

## Acceptance Criteria
- All requirements have passing tests
- Existing database-pgsql and pubsub-pgsql tests still pass

## Implementation Notes
(Left blank - filled in by programmer during implementation)
