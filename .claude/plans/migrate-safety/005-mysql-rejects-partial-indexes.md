# Task 005: MySql generator rejects partial indexes

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
MySQL has no partial indexes. Generating an index with `where` must throw a clear exception instead of silently creating a full index.

## Context
- Related files: packages/database-mysql/src/Sql/MySqlGenerator.php, packages/database/src/Exceptions/MigrationException.php

## Requirements (Test Descriptions)
- [x] `it throws when adding a partial index on mysql`
- [x] `it throws when creating a table with a partial index on mysql`
- [x] `it names the index and suggests an alternative in the exception`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
