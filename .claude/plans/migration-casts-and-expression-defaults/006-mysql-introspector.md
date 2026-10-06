# Task 006: MySQL Introspector — Expression and Literal Defaults

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
A default whose `EXTRA` contains `DEFAULT_GENERATED` is returned as an `Expression`; a literal string that would read as a shortcut is returned as a `Literal`, except timestamp keywords on servers that do not report `DEFAULT_GENERATED`.

## Context
- Related files: packages/database-mysql/src/Introspection/MySqlIntrospector.php, packages/database-mysql/tests/Introspection/MySqlIntrospectorTest.php

## Requirements (Test Descriptions)
- [x] `it reads a DEFAULT_GENERATED default as an expression`
- [x] `it reads a literal default that looks like a function as a literal`
- [x] `it keeps a plain string default as a string`
- [x] `it keeps CURRENT_TIMESTAMP without DEFAULT_GENERATED as a string`

## Acceptance Criteria
- All requirements have passing tests
