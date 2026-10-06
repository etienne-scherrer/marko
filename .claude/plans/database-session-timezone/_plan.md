# Plan: Database Session Time Zone

## Created
2026-10-06

## Status
completed

## Objective
Pin the database session time zone to `database.timezone` on every connect for the MySQL/MariaDB and PostgreSQL drivers (option C of #304), so `TIMESTAMP` conversion, `CURRENT_TIMESTAMP` and `NOW()` agree with the zone Marko writes datetimes in, and fail loudly when MySQL/MariaDB cannot load a named zone.

## Related Issues
Closes #304

## Discovery Notes
- `DatabaseTimezoneConfig` (marko/database) reads `timezone` from `config/database.php` on its own (default `UTC`); the connections only see `DatabaseConfig`.
- `MySqlConnection::connect()` builds a PDO with SSL options and runs nothing afterwards; `PgSqlConnection::connect()` runs `SET NAMES` after `createPdo()`.
- Both connections are built either by autowiring (`ConnectionInterface => MySqlConnection`) or through `ConnectionFactoryInterface::make(DatabaseConfig)`; `database-readwrite` builds its nodes with `DatabaseConfig::fromArray()` on per-node arrays that never carry the top-level `timezone`.
- Verified against MySQL 8.4 and MariaDB 11.8: `PDO\Mysql::ATTR_INIT_COMMAND` with `SET time_zone = 'Foo/Bar'` makes the PDO constructor throw a `PDOException` with code 1298 ("Unknown or incorrect time zone"). Both official images load the zone tables at init, so named zones work in CI.
- PHP `DateTimeZone` accepts abbreviations (`EST`, `CEST`, `utc`) and offsets (`+05:30`) as fixed-offset zones (`getLocation() === false`). PostgreSQL reads a bare `'+05:30'` as a POSIX zone with the inverted sign, so offsets must go through `SET TIME ZONE INTERVAL '+05:30' HOUR TO MINUTE`.
- Unit-test fakes back the connections with SQLite PDOs: the PgSQL ones skip `SET NAMES`, and several MySQL ones forward the PDO options to SQLite (which rejects the MySQL init command attribute).

## Scope

### In Scope
- `DatabaseConfig::$timezone` (read from the same `timezone` key, default `UTC`, same validation as `DatabaseTimezoneConfig`) and `DatabaseConfig::fixedTimezoneOffset()`
- MySQL: `PDO\Mysql::ATTR_INIT_COMMAND` = `SET time_zone = '<offset or name>'` (runs on every new PDO, so reconnects too); 1298 → `ConnectionException::unknownTimezone()`
- PostgreSQL: `SET TIME ZONE ...` after `SET NAMES`; an invalid zone → `ConnectionException::unknownTimezone()`
- `database-readwrite` passes the top-level `database.timezone` to every node config
- Unit tests for the statement on connect (UTC, named zone, fixed offset) and the loud failure
- Integration tests on MySQL, MariaDB and PostgreSQL with a non-UTC server zone, covering a `TIMESTAMP` column and a `DEFAULT CURRENT_TIMESTAMP` column
- Docs: database.md, database-mysql.md, database-pgsql.md, database-readwrite.md (if it mentions timezone), queue-database.md operational note, upgrade note

### Out of Scope
- An opt-out config key (option D)
- Converting existing data automatically

## Success Criteria
- [ ] Both drivers pin the session zone from `database.timezone` on every connect, including reconnects
- [ ] An unknown/unloaded zone fails at connect with a `ConnectionException` naming the zone and the fix
- [ ] Real-database tests pass on MySQL 8.4, MariaDB 11.8 and PostgreSQL 17
- [ ] Docs updated, including an upgrade note
- [ ] All tests passing
- [ ] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | DatabaseConfig carries the database timezone | - | completed |
| 002 | MySQL connection pins the session time zone | 001 | completed |
| 003 | PostgreSQL connection pins the session time zone | 001 | completed |
| 004 | Read/write nodes use database.timezone | 001 | completed |
| 005 | MySQL/MariaDB integration tests | 002 | completed |
| 006 | PostgreSQL integration tests | 003 | completed |
| 007 | Documentation and upgrade note | 002, 003, 004 | completed |

## Architecture Notes
- The zone is carried on `DatabaseConfig` (rather than injecting `DatabaseTimezoneConfig` into connections) so the connection constructors, factories and every existing caller keep their signatures; the factories already receive the `DatabaseConfig` they build from.
- Contract (task 001): `DatabaseConfig::$timezone` is a `string` holding the canonical `DateTimeZone::getName()`; `fixedTimezoneOffset(): ?string` returns `'+HH:MM'` for UTC, offsets and abbreviations, and null for region zones. `DatabaseTimezoneConfig::resolveTimezone()` becomes `public static`.
- PgSQL `connect()` assigns `$this->pdo` only after `SET NAMES`/`SET TIME ZONE` succeed, so a failed session setup never leaves a half-configured connection cached.
- Fixed-offset zones are sent as numeric offsets on both drivers so the database applies exactly the offset PHP formats with, and MySQL needs no zone tables for them.

## Risks & Mitigations
- Breaking change for non-UTC MySQL servers with existing `TIMESTAMP` data: upgrade note with a conversion query; PR labelled `breaking`.
- Integration tests change a server-wide default zone: restored in `finally`, the Integration job runs serially.
