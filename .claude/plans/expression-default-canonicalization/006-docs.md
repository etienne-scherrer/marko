# Task 006: Docs

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005
**Retry count**: 0

## Description
Describe how complex expression defaults are compared and drop the "write it the way the database reports it" advice.

## Context
- Related files: packages/docs-markdown/docs/packages/database.md ("Column Defaults"), database-pgsql.md, database-mysql.md ("Expression Defaults"); docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `database.md explains that db:diff asks the database how it would store a rewritten expression`
- [x] `database-pgsql.md and database-mysql.md describe the temporary-table probe and the rejected-expression error`
- [x] `no doc tells developers to write the expression the way the database reports it`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS

## Implementation Notes
Implemented with strict TDD; see the PR description for the design notes.
