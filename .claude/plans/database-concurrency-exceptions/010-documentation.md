# Task 010: Documentation

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005, 006, 008, 009
**Retry count**: 0

## Description
Add "Concurrency errors and retries" to database.md (table of codes, retry semantics, no HTTP mapping and why, upgrade note for implementors), list codes in database-pgsql.md / database-mysql.md, update the transaction signature in database-readwrite.md, and check the package READMEs stay slim pointers.

## Context
- Related files: packages/docs-markdown/docs/packages/database*.md, packages/database*/README.md, docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `docs describe each exception, its codes and whether it is retryable`
- [x] `docs describe transaction() attempts semantics and the nested rule`
- [x] `docs explain why the exceptions have no HTTP status`
- [x] `docs explain that after-rollback callbacks run on each failed attempt and after-commit callbacks only for the attempt that commits`
- [x] `docs warn that under RefreshDatabase/TestDatabase every transaction() call is nested, so attempts never retries there` (point to testing retries against an unwrapped connection or by catching TransactionConflictException)

## Acceptance Criteria
- Docs follow DOCS-STANDARDS

## Implementation Notes
database.md: new "Concurrency Errors and Retries" section (table, retry rules, RefreshDatabase note, no HTTP status, implementor upgrade note), exceptions table/hierarchy, row-lock and transaction cross-links, replaced the old SQLSTATE retry example. database-pgsql.md, database-mysql.md, database-readwrite.md updated. READMEs are slim pointers with no affected content.
