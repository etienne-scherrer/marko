# Task 004: Generator Tests Show Shortened Names

**Status**: completed
**Depends on**: 002, 003
**Retry count**: 0

## Description
Feed a diff computed from long table and column names through the MySQL and PostgreSQL generators and assert the generated SQL carries the shortened names. Test-only: generator source is #323's territory.

## Context
- Related files: packages/database-mysql/tests/Sql/MySqlGeneratorTest.php, packages/database-pgsql/tests/Sql/PgSqlGeneratorTest.php
- The derived `_unique` index only shows up in an ALTER diff: compute the diff with `DiffCalculator` from an entity table whose long column is `unique: true` against a database table that already has that column non-unique. A CREATE TABLE or ADD COLUMN diff declares UNIQUE inline and never uses the derived name.
- The FK name comes from `SchemaBuilder` (or a `ForeignKey` built with `IdentifierName::derive(..., prefix: 'fk_')`).
- Assert the literal shortened name in the SQL (hardcoded string, not recomputed via `IdentifierName`), so a scheme change shows up here too.
- Generator quoting may change under #323. Assert on the name substring, not the full statement, where possible.

## Requirements (Test Descriptions)
- [x] `it creates the unique index of a long table and column under its shortened name` (MySQL)
- [x] `it adds a long foreign key under its shortened name` (MySQL)
- [x] `it creates the unique index of a long table and column under its shortened name` (PostgreSQL)
- [x] `it adds a long foreign key under its shortened name` (PostgreSQL)

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Done.
