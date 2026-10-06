# Task 001: Emit Sequence Type Change for Auto-Increment Type Changes

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
When an auto-increment column's type changes between `SMALLINT`, `INTEGER` and `BIGINT`, `PgSqlGenerator` emits one `DO` block that fails loudly when the column owns no sequence, alters the column, and alters the owned sequence to the new type. Up and down migrations both use it.

## Context
- Related files: `packages/database-pgsql/src/Sql/PgSqlGenerator.php`, `packages/database-pgsql/tests/Sql/PgSqlGeneratorTest.php`
- Patterns to follow: `generateModifyColumnIfChanged()`; existing `pgsqlModifyDiff()` test helper
- One statement, because the `Migrator` runs statements without a wrapping transaction
- `pg_get_serial_sequence(table, column)`: the FIRST argument is parsed as an identifier (lower-cased unless double-quoted), so pass the table as `'"<table>"'` (double quotes inside the string literal, matching the `"$table"` the generator emits everywhere). The SECOND argument is taken literally, so pass the bare column name (no double quotes)
- The returned sequence name is already quoted and schema-qualified: run the sequence change with `EXECUTE format('ALTER SEQUENCE %s AS <TYPE>', sequence_name)`, using `%s` and never `%I`
- The complete `ALTER TABLE` for the column (type change plus any nullability change produced for it) goes inside the `DO` block; never a second statement
- Existing tests that currently expect a plain `ALTER TABLE` for an auto-increment key whose type changes must be updated to the `DO` block: "does not drop NOT NULL on an auto-increment primary key declared nullable in PHP" and the nextval-default widening test (around line 800 of `PgSqlGeneratorTest.php`). Do not change the generator to keep them passing
- The remediation goes in the `RAISE ... USING MESSAGE` text itself (table, column, and how to make the sequence owned by the column). Do not rely on `HINT` reaching the translated exception

## Requirements (Test Descriptions)
- [x] `it widens the owned sequence with an auto-increment key in one statement`
- [x] `it narrows the owned sequence back in the down migration`
- [x] `it changes the sequence of a smallint auto-increment key widened to integer`
- [x] `it raises an error naming the table and column when the key owns no sequence`
- [x] `it keeps a plain ALTER TABLE for an auto-increment key whose type does not change`
- [x] `it escapes single quotes in the table and column names it passes to pg_get_serial_sequence and in the error message`
- [x] `it double-quotes the table name passed to pg_get_serial_sequence so a mixed-case table is found`
- [x] `it puts a nullability change of the auto-increment column inside the same DO block`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No change for non-auto-increment columns

## Implementation Notes
`generateModifyColumnIfChanged()` keeps its `?string` return; when an auto-increment column changes between SMALLINT/INTEGER/BIGINT it returns `generateSequenceFollowingTypeChange()`, one DO block (owned-sequence check with RAISE ... USING MESSAGE, the full ALTER TABLE, `EXECUTE format('ALTER SEQUENCE %s AS <TYPE>', ...)`). Names in string literals are escaped by `quoteStringLiteral()`.
