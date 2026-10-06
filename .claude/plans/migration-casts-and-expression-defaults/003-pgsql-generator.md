# Task 003: PostgreSQL Generator — Expression Defaults and USING Casts

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Emit `Expression` and shortcut defaults raw and `Literal` defaults quoted; add `USING "col"::TYPE` to every type change, and drop the old default before the type change (restoring the target default after) so a default that cannot be cast never blocks the migration.

## Context
- Related files: packages/database-pgsql/src/Sql/PgSqlGenerator.php, packages/database-pgsql/tests/Sql/PgSqlGeneratorTest.php

## Requirements (Test Descriptions)
- [x] `it emits an unquoted gen_random_uuid() default`
- [x] `it emits an explicit expression default raw`
- [x] `it quotes a literal default that looks like a function`
- [x] `it keeps an existing CURRENT_TIMESTAMP default unquoted`
- [x] `it casts with USING when changing varchar to integer`
- [x] `it casts with USING when changing integer to varchar in the down migration`
- [x] `it drops and restores the default around a type change`
- [x] `it does not alter the default when only the default's representation differs`
- [x] `it does not drop or restore the sequence default when changing the type of an auto-increment column`
- [x] `it drops the old default without restoring one when the target column has no default`
- [x] `it emits DROP DEFAULT, TYPE ... USING and SET DEFAULT in that order within one ALTER TABLE`

## Implementation Notes
- Replace the `$column->default !== $oldColumn->default` check (line ~497) with `!$column->hasSameDefaultAs($oldColumn)` from task 002; `!==` is always true for two distinct `Expression` instances and would emit a SET DEFAULT on every diff.
- Auto-increment columns (SERIAL/BIGSERIAL, e.g. int -> bigint on `id`): the default is `nextval(...)`. Dropping it and "restoring the target default" (null for auto-increment) would detach the sequence. Never drop/restore the default for `autoIncrement` columns; `nextval` returns bigint and survives the cast.
- PostgreSQL runs DROP DEFAULT before ALTER TYPE and SET DEFAULT after it regardless of subcommand order within one statement, but emit them in that order anyway for readability.
- Cast target in `USING "col"::TYPE` must be `mapType($column)` (never the SERIAL name).
## Acceptance Criteria
- All requirements have passing tests
