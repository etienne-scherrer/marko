# Plan: Expression Default Canonicalization

## Created
2026-10-06

## Status
completed

## Objective
Stop expression column defaults that the database rewrites (PostgreSQL `now() + interval '1 day'` stored as `(now() + '1 day'::interval)`, MySQL `CONCAT('a', 'b')` stored as `concat(_utf8mb4'a',_utf8mb4'b')`) from showing up as changed on every `db:diff` / `db:migrate`, by asking the database how it would store the entity's expression.

## Related Issues
Closes #306

## Discovery Notes
- `Column::hasSameDefaultAs()` compares expressions through `Expression::normalize()` (lowercase, whitespace, outer parentheses). Anything the database deparses differently is a perpetual diff.
- Both commands (`DiffCommand::execute()`, `MigrateCommand::calculateDiff()`) build the entity schema and the database schema, then call `DiffCalculator::calculate()`. `DiffCalculator` stays a pure function.
- Probing on real servers (PostgreSQL 17, MySQL 8.4, MariaDB 11.8) showed:
  - PostgreSQL reports a temporary table's `column_default` exactly as for a real table (same `pg_get_expr` deparser), so a temp-table probe of the same type compares byte for byte.
  - MySQL keeps temporary tables out of `information_schema.COLUMNS`. `SHOW COLUMNS` on a temporary table reports the default without the backslash escaping `information_schema` applies (`concat(_utf8mb4'a',...)` vs `concat(_utf8mb4\'a\',...)`) and with one extra pair of outer parentheses. So the stored text of the real column is unescaped and both sides are compared without wrapping parentheses.
  - MariaDB keeps temporary tables out of `information_schema.COLUMNS` too, and reports identical text from `SHOW COLUMNS` (temporary) and `information_schema` (real), without escaping.
- Deviation from the ticket's proposed `canonicalDefaultExpression(string $sql, Column $column): string`: because MySQL reports a temporary table's default in a different format than `information_schema` does for the real column, the driver cannot return "the text `getColumns()` will report". The driver method instead answers the actual question, `matchesStoredDefault(table, column, expression): bool`, comparing both sides in one format that the driver controls. It lives on a separate `ExpressionDefaultMatcherInterface` so third-party introspectors do not break.

## Scope

### In Scope
- `ExpressionDefaultMatcherInterface` in `marko/database` (non-breaking: separate interface, the canonicalizer checks for it)
- `ExpressionDefaultCanonicalizer` that, before the diff, swaps an entity column's `Expression` default for the database's default when the database says it would store the same thing
- PostgreSQL implementation (temp table in a rolled-back transaction, same `information_schema` query)
- MySQL/MariaDB implementation (`CREATE TEMPORARY TABLE`, `SHOW COLUMNS`, `DROP TEMPORARY TABLE`)
- `MigrationException` naming table, column and expression when the database rejects the expression
- Wiring in `DiffCommand` and `MigrateCommand`
- Docs: `database.md`, `database-pgsql.md`, `database-mysql.md`

### Out of Scope
- Re-implementing PostgreSQL/MySQL deparser rules in PHP
- Validating expressions of columns being added (no database column to compare with)
- PgSqlGenerator auto-increment changes (#307), Repository/EntityHydrator (#305)

## Success Criteria
- [x] PostgreSQL: `default: new Expression("now() + interval '1 day'")` diffs as empty right after creation
- [x] MySQL 8.0.13+: `(CONCAT('a', 'b'))` and `(CURRENT_TIMESTAMP + INTERVAL 1 DAY)` diff as empty right after creation
- [x] Changing the expression still produces a diff and the right SET DEFAULT / MODIFY COLUMN
- [x] A rejected expression fails at diff time with a `MigrationException` naming the column and expression
- [x] No probe runs for columns whose defaults already compare equal (counting fake)
- [x] Docs updated, "write it the way the database reports it" advice removed
- [x] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Matcher interface, Expression::unwrap, rejected-expression exception | - | completed |
| 002 | ExpressionDefaultCanonicalizer | 001 | completed |
| 003 | Wire canonicalizer into DiffCommand and MigrateCommand | 001, 002 | completed |
| 004 | PostgreSQL matcher implementation + integration tests | 001, 002 | completed |
| 005 | MySQL/MariaDB matcher implementation + integration tests | 001, 002 | completed |
| 006 | Docs | 001, 002, 003, 004, 005 | completed |
| 007 | Warn instead of failing when the post-migration drift check cannot probe (coordinator decision) | 003, 004, 005 | completed |

## Architecture Notes
- The canonicalizer only probes a column when: the entity default is an `Expression`, the database column exists with a non-null default, the defaults do not already compare equal, and the column is otherwise equal (a column modified for another reason keeps the entity's expression so the generated SQL uses what the developer wrote).
- When the database says the expression matches, the entity column's default becomes the database column's default, so `DiffCalculator` sees them as equal.
- The probe uses the real column's native type (`format_type()` on PostgreSQL, `COLUMN_TYPE` on MySQL), since a cast in the deparsed text depends on it.

## Risks & Mitigations
- Probe runs inside a caller's transaction: PostgreSQL uses `beginTransaction()`/`rollback()` (a savepoint when nested); MySQL's `CREATE/DROP TEMPORARY TABLE` never commits implicitly.
- Leftover probe table after a failure: dropped/rolled back in `finally`, and MySQL drops any leftover before creating.
