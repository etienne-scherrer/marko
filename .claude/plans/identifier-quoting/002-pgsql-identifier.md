# Task 002: PgSqlIdentifier quoting class

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `Marko\Database\PgSql\Sql\PgSqlIdentifier` with a static `quote(string $identifier): string` that wraps a name in double quotes and doubles embedded double quotes. It is the single PostgreSQL quoting rule.

## Context
- Related files: `packages/database-pgsql/src/Sql/PgSqlIdentifier.php` (new)
- Patterns to follow: `PgSqlQueryBuilder::quoteIdentifier()`

## Requirements (Test Descriptions)
- [x] `it wraps a plain name in double quotes`
- [x] `it quotes each part of a table.column name`
- [x] `it doubles an embedded double quote`
- [x] `it quotes a reserved word`
- [x] `it preserves mixed case`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
