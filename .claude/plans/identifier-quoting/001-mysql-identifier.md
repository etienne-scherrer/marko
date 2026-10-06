# Task 001: MySqlIdentifier quoting class

**Status**: pending
**Depends on**: none
**Retry count**: 0

## Description
Add `Marko\Database\MySql\Sql\MySqlIdentifier` with a static `quote(string $identifier): string` that wraps a name in backticks and doubles embedded backticks. It is the single MySQL/MariaDB quoting rule the generator, query builder, introspector and connection share.

## Context
- Related files: `packages/database-mysql/src/Sql/MySqlIdentifier.php` (new), `packages/database/src/Query/IdentifierValidator.php` (`escapeDelimiter()`)
- Patterns to follow: `MySqlQueryBuilder::quoteIdentifier()` (handles `table.column`)

## Requirements (Test Descriptions)
- [ ] `it wraps a plain name in backticks`
- [ ] `it quotes each part of a table.column name`
- [ ] `it doubles an embedded backtick`
- [ ] `it quotes a reserved word`
- [ ] `it keeps mixed case and double quotes as written`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
