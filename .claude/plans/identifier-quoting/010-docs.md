# Task 010: Docs

**Status**: pending
**Depends on**: 001, 002, 003, 004, 005, 006, 007, 008, 009, 011
**Retry count**: 0

## Description
Document `quoteIdentifier()` in `database.md` (ConnectionInterface table, driver-authoring section, a note that reserved words and mixed case are safe in entity column names) and the identifier classes / connection method in `database-mysql.md` and `database-pgsql.md`. Add an upgrade note covering the two behavior changes.

## Context
- Related files: `packages/docs-markdown/docs/packages/database.md`, `database-mysql.md`, `database-pgsql.md`; `docs/DOCS-STANDARDS.md`

## Requirements (Test Descriptions)
- [ ] `it lists quoteIdentifier in the ConnectionInterface table`
- [ ] `it lists quoteIdentifier in the driver-authoring section`
- [ ] `it documents MySqlIdentifier and PgSqlIdentifier in the driver API tables`
- [ ] `it notes the upgrade impact of identifier quoting`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS; DocsClassReferenceTest passes

## Implementation Notes
- Upgrade note content: (1) third-party `ConnectionInterface` implementations must add `quoteIdentifier()`; (2) on PostgreSQL, Repository/DataMigration SQL is now quoted, so an explicitly mixed-case `#[Table]`/`#[Column(name: ...)]` name is case-sensitive. A table created by a hand-written migration with unquoted mixed-case names (folded to lower case by PostgreSQL) no longer matches. Default snake_case names are unaffected.
