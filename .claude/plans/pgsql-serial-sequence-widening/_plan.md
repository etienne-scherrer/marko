# Plan: PostgreSQL Serial Sequence Widening

## Created
2026-10-06

## Status
completed

## Objective
When a generated migration changes the integer type of a PostgreSQL auto-increment key, change the type of the sequence that feeds it in the same statement, so widening `integer` to `bigint` really lets ids pass 2,147,483,647.

## Related Issues
Closes #307

## Discovery Notes
- `PgSqlGenerator::generateModifyColumnIfChanged()` emits only `ALTER COLUMN ... TYPE` for an auto-increment key; the `SERIAL` sequence stays `AS integer` with `MAXVALUE 2147483647`.
- The introspector drops the `nextval(...)` default of a serial column, so the generator has no sequence name. `pg_get_serial_sequence()` finds it at run time.
- `Migrator` does not wrap a migration in a transaction and `PgSqlConnection::execute()` prepares a single statement, so "same transaction" can only be guaranteed by emitting ONE statement: a `DO` block that checks the sequence, alters the column and alters the sequence.
- `MigrationGenerator` writes statements into nowdoc blocks, so `$$` survives as-is.
- Probe on PostgreSQL 17: an identity column's sequence already follows `ALTER COLUMN ... TYPE` (integer -> bigint -> smallint). `ALTER SEQUENCE ... AS` on an identity sequence is allowed and is then a no-op, so serial and identity columns can share the same statement (the generator cannot tell them apart; `Column` has only `autoIncrement`).

## Scope

### In Scope
- `PgSqlGenerator`: auto-increment column whose type changes between `SMALLINT`/`INTEGER`/`BIGINT` -> a single `DO` block (raise naming table and column when no owned sequence, `ALTER TABLE`, `ALTER SEQUENCE ... AS <type>`), up and down
- Unit tests for the emitted statements
- Integration tests against real PostgreSQL (widen, setval to 2147483647, insert gets 2147483648; down narrows; down fails loudly when value too big; smallint -> integer; unowned sequence error; identity column; generated migration file run through Migrator)
- Docs page `database-pgsql.md` "Type Changes"

### Out of Scope
- MySQL (unaffected)
- Non-auto-increment columns
- Introspector / diff changes (#306)
- Adding transactions to `Migrator`

## Success Criteria
- [ ] Widening a SERIAL key leaves the sequence `AS bigint` with max 9223372036854775807
- [ ] Down narrows the sequence and fails loudly when the value does not fit
- [ ] smallint -> integer behaves the same
- [ ] Unowned sequence fails naming table and column
- [ ] Identity columns behave correctly
- [ ] Generated migration file runs through the Migrator
- [ ] All tests passing, `composer ci` green
- [ ] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Emit sequence type change for auto-increment type changes | - | completed |
| 002 | Integration tests against real PostgreSQL | 001 | completed |
| 003 | Docs update | 001 | completed |

## Architecture Notes
- Keep the change inside the auto-increment branch of `generateModifyColumnIfChanged()`; its `?string` return stays.
- Use `RAISE EXCEPTION USING MESSAGE = ...` so `%` in names is never interpreted. Put the remediation in the MESSAGE text itself: the exception translator wraps the `PDOException`, and a `HINT` is not guaranteed to reach the message.
- Escape single quotes in every name embedded in an SQL string literal (the `pg_get_serial_sequence()` arguments and the error message).
- `pg_get_serial_sequence()` parses its first argument as an identifier: pass `'"<table>"'`. Its second argument is taken literally: pass the bare column name.
- Change the sequence with `EXECUTE format('ALTER SEQUENCE %s AS <TYPE>', sequence_name)`, using `%s` because the returned name is already quoted and schema-qualified.
- Integration tests use their own table and file (parallel runs) and drop the detached sequence on cleanup (`OWNED BY NONE` sequences survive `DROP TABLE`).

## Risks & Mitigations
- PDO placeholder parsing inside the `DO` body: avoid `?` and `:name` in the message text; verified by integration tests.
- Partial migration if column and sequence ran separately: avoided by the single `DO` statement.
