# Task 005: PostgreSQL Introspector — Expression and Literal Defaults

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`parseDefault()` returns quoted literals as strings (unescaping `''`), booleans and numbers as before, `NULL` as no default, and every other default as an `Expression`. A literal string that would read as a shortcut is returned as a `Literal`.

## Context
- Related files: packages/database-pgsql/src/Introspection/PgSqlIntrospector.php, packages/database-pgsql/tests/Introspection/PgSqlIntrospectorTest.php

## Requirements (Test Descriptions)
- [x] `it reads a function default as an expression`
- [x] `it reads CURRENT_TIMESTAMP as an expression`
- [x] `it reads a quoted literal that looks like a function as a literal`
- [x] `it unescapes doubled quotes in a string literal default`
- [x] `it reads an explicit NULL default as no default`

## Acceptance Criteria
- All requirements have passing tests
