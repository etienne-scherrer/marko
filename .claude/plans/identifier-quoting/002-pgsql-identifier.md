# Task 002: PgSqlIdentifier quoting class

**Status**: pending
**Depends on**: none
**Retry count**: 0

## Description
Add `Marko\Database\PgSql\Sql\PgSqlIdentifier` with a static `quote(string $identifier): string` that wraps a name in double quotes and doubles embedded double quotes. It is the single PostgreSQL quoting rule.

## Context
- Related files: `packages/database-pgsql/src/Sql/PgSqlIdentifier.php` (new)
- Patterns to follow: `PgSqlQueryBuilder::quoteIdentifier()`

## Requirements (Test Descriptions)
- [ ] `it wraps a plain name in double quotes`
- [ ] `it quotes each part of a table.column name`
- [ ] `it doubles an embedded double quote`
- [ ] `it quotes a reserved word`
- [ ] `it preserves mixed case`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
