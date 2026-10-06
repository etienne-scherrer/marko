# Plan: Schema Identifier Length

## Created
2026-10-06

## Status
completed

## Objective
Keep every index and foreign key name the schema layer emits within 63 bytes: shorten names Marko derives (`_unique`, `_index`, `fk_`) deterministically with a hash, and reject over-long names the developer declares with `#[Index(name:)]`.

## Related Issues
Closes #315

## Discovery Notes
- Three derived names are built by concatenation with no length check: `<table>_<column>_unique` (`DiffCalculator::deriveUniqueColumnIndexes()`), `<table>_<column>_index` (`DiffCalculator::foreignKeyReplacementIndexes()`) and `fk_<table>_<column>` (`SchemaBuilder::buildForeignKeys()`).
- Declared names flow through `SchemaBuilder::buildIndex()` (called from `build()` and from `SchemaRegistry` for extender indexes) unchecked.
- MySQL rejects identifiers over 64 characters (error 1059); PostgreSQL truncates to 63 bytes with a NOTICE, which breaks name-matched declared indexes (the diff never settles) and makes long names that share a 63-byte prefix collide.
- `EntityException` already holds schema-build errors for entities and extends `MarkoException`.
- The issue is a decision issue (label `question`); this plan implements its recommendation: shorten derived names, throw for declared names.
- #323 (identifier quoting) runs in parallel and edits the SQL generators; this plan leaves generator source alone and only adds generator tests.

## Scope

### In Scope
- `Marko\Database\Schema\IdentifierName` helper: the 63-byte limit, byte length, deterministic shortening (`<prefix><cut body>_<crc32b of full name><suffix>`), multibyte-safe cut.
- Use the helper at the three derivation sites.
- `EntityException::indexNameTooLong()` thrown from `SchemaBuilder::buildIndex()`; `buildIndex()` takes the entity class for the message.
- Unit tests (DiffCalculator, SchemaBuilder, IdentifierName), generator tests (MySQL, PostgreSQL), real-database integration tests (PostgreSQL, MySQL, MariaDB via the MySQL suite).
- Docs: database.md, database-mysql.md, database-pgsql.md.

### Out of Scope
- Identifier quoting in the SQL generators (#323).
- Explicit foreign key names (not accepted by any attribute today).
- Renaming existing indexes or foreign keys whose names already fit.

## Success Criteria
- [x] Derived names over 63 bytes are shortened deterministically, stay ≤ 63 bytes, and differ for inputs sharing a long prefix
- [x] Names that already fit are unchanged (`users_email_unique`)
- [x] Declared `#[Index(name:)]` over 63 bytes throws `EntityException` with context and suggestion; measured in bytes
- [x] Generated SQL on MySQL and PostgreSQL carries the shortened names; real databases apply them and the diff settles
- [x] Docs explain the 63-byte rule and the shortened form
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | IdentifierName helper | - | completed |
| 002 | Shorten derived index names in DiffCalculator | 001 | completed |
| 003 | Shorten fk_ names and validate declared index names in SchemaBuilder | 001 | completed |
| 004 | Generator tests show shortened names | 002, 003 | completed |
| 005 | Real-database integration tests | 002, 003 | completed |
| 006 | Documentation | 002, 003 | completed |

## Architecture Notes
- One helper owns the length rule; call sites pass prefix, body and suffix.
- Hash input is the full untruncated name, so two names that share their first 63 bytes still differ.
- `mb_strcut()` cuts the body at a byte boundary without splitting a UTF-8 character.
- Contract: `IdentifierName::derive(string $body, string $prefix = '', string $suffix = ''): string`, `fits()`, `byteLength()`, `MAX_BYTES = 63` (task 001). `SchemaBuilder::buildIndex(IndexMetadata $metadata, string $entityClass): Index`; `EntityException::indexNameTooLong(string $entityClass, string $indexName, int $byteLength)` (task 003).
- The declared-name check lives in `buildIndex()`, so it fires on schema paths (db:diff/db:migrate), not on runtime entity hydration.

## Risks & Mitigations
- Changing a derived name that already fits would rename live indexes: the helper returns names ≤ 63 bytes untouched (regression test).
- Merge conflicts with #323 in generator tests: only append new test cases; rebase before opening the PR.
- A future change to the hash scheme would rename every shortened index/FK in production: golden-value tests (001, 004) pin the literal output.
- PostgreSQL silently truncates and the diff matches derived uniques/FKs by column, so the PG integration test must assert the introspected names, not just an empty diff (005).
