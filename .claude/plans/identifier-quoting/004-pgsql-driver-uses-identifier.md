# Task 004: PostgreSQL generator, query builder and introspector use PgSqlIdentifier; safe DO tag

**Status**: pending
**Depends on**: 002
**Retry count**: 0

## Description
Replace every inline `"\"$name\""` in `PgSqlGenerator` with `PgSqlIdentifier::quote()`, make `PgSqlQueryBuilder::quoteIdentifier()` and `PgSqlIntrospector`'s private quoter delegate to it, and make the serial-sequence `DO` block use a dollar-quote tag that does not occur in its body, so a name containing `$$` cannot end the block early.

## Context
- Related files: `packages/database-pgsql/src/Sql/PgSqlGenerator.php`, `packages/database-pgsql/src/Query/PgSqlQueryBuilder.php`, `packages/database-pgsql/src/Introspection/PgSqlIntrospector.php`, `packages/database-pgsql/tests/Sql/PgSqlGeneratorTest.php`

## Requirements (Test Descriptions)
- [ ] `it escapes a double quote in a table name in CREATE TABLE`
- [ ] `it escapes a double quote in column, index, constraint and referenced names`
- [ ] `it escapes a double quote in ALTER COLUMN statements`
- [ ] `it dollar-quotes the sequence DO block with a tag that does not occur in its body`
- [ ] `it picks another tag when a name contains the default tag`
- [ ] `it has no inline double-quote identifier quoting in the generator, query builder or introspector`
- [ ] `it passes the quoted table name and the unquoted column name to pg_get_serial_sequence`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- `pg_get_serial_sequence()` parses its first argument as SQL, so the string literal holds `PgSqlIdentifier::quote($table)` (embedded `"` doubled); the second argument is taken literally (case preserved), so it holds the raw column name.
- The dollar-quote tag must be chosen against the *whole* DO body, including the `RAISE EXCEPTION` message (which embeds table/column names) and `$alterTable`.
- The private `quoteIdentifiers()` helper maps through `PgSqlIdentifier::quote()`.
- The "no inline quoting" test must target identifier-quoting concatenations (`"\"$`), not every `"` — exception/RAISE message text and string literals are fine. Prefer behavior tests over a raw source grep.
- Do not edit `ConnectionInterface` test stubs in this task (task 005 owns them).
