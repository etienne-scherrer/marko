# Task 002: MariaDB introspection: JSON columns and ON UPDATE spelling

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
MariaDB stores `JSON` as `LONGTEXT` with an automatic `CHECK (json_valid(col))`, so `MySqlIntrospector` reports `longtext` and every JSON column diffs forever. Report such columns as `json`. Also normalise MariaDB's `on update current_timestamp()` to `CURRENT_TIMESTAMP` as the default already is.

## Context
- Related files: packages/database-mysql/src/Introspection/MySqlIntrospector.php, packages/database-mysql/tests/Introspection/*
- MariaDB: `information_schema.CHECK_CONSTRAINTS` has CONSTRAINT_SCHEMA, TABLE_NAME, CONSTRAINT_NAME, CHECK_CLAUSE (`json_valid(\`meta\`)`)

- `MySqlIntrospectorTest.php` already contains a `mariaDbLongtextColumns(array $checks, string $version)` helper (mock matches `information_schema.CHECK_CONSTRAINTS`); reuse it. The mock matches SQL by substring in declaration order, so the CHECK_CONSTRAINTS query must not contain `information_schema.columns`.

## Implementation Constraints
- A detected JSON column is `type: 'json'`, `nativeType: 'json'`, `length: null` (MariaDB reports CHARACTER_MAXIMUM_LENGTH 4294967295) and `collation: null` (MariaDB reports `utf8mb4_bin`).
- Match the clause to the exact column: `json_valid(\`meta\`)` (case-insensitive function name, optional surrounding whitespace) marks `meta` only. It must not mark `meta2`, and a compound clause such as `json_valid(\`meta\`) and ...` does not count.
- Send at most one CHECK_CONSTRAINTS query per `getColumns()` call (bound by `CONSTRAINT_SCHEMA = ?` and `TABLE_NAME = ?`), and only when the server is MariaDB and at least one column is `longtext`. Never send it per column.
- ON UPDATE: `current_timestamp()` becomes `CURRENT_TIMESTAMP` and `current_timestamp(3)` becomes `CURRENT_TIMESTAMP(3)` (keep the precision, the same rule `parseMariaDbDefault` uses). MySQL spelling stays unchanged.
- The integration test runs on BOTH servers (no server check, so no dependency on task 001). Put it in `SchemaDiffSettlesTest.php` or `ExpressionDefaultsTest.php`.

## Requirements (Test Descriptions)
- [ ] `it reports a MariaDB longtext column with a json_valid check as json`
- [ ] `it reports a MariaDB json column with no length and no collation`
- [ ] `it keeps a MariaDB longtext column without a json_valid check as longtext`
- [ ] `it does not treat a json_valid check on another column as json`
- [ ] `it does not read check constraints on MySQL`
- [ ] `it does not read check constraints when MariaDB has no longtext column`
- [ ] `it normalises MariaDB on update current_timestamp() to CURRENT_TIMESTAMP`
- [ ] `it keeps the precision of MariaDB on update current_timestamp(3)`
- [ ] `it diffs a json column clean after creating it` (integration, both servers)

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
