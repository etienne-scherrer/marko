# Task 003: MySqlIntrospector reads native type, collation, ON UPDATE

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Read `COLUMN_TYPE`, `COLLATION_NAME` and the `ON UPDATE` part of `EXTRA` into the new `Column` fields.

## Context
- Related files: packages/database-mysql/src/Introspection/MySqlIntrospector.php, packages/database-mysql/tests/Introspection/MySqlIntrospectorTest.php

## Requirements (Test Descriptions)
- [x] `it reads the native column type including precision and unsigned`
- [x] `it reads the column collation`
- [x] `it reads the ON UPDATE expression from EXTRA`
- [x] `it leaves the on-update expression null when EXTRA has none`
- [x] `it reads the ON UPDATE expression when EXTRA also holds DEFAULT_GENERATED` (`DEFAULT_GENERATED on update CURRENT_TIMESTAMP` -> `CURRENT_TIMESTAMP`)
- [x] `it reads a fractional ON UPDATE expression` (`on update CURRENT_TIMESTAMP(3)` -> `CURRENT_TIMESTAMP(3)`)

## Acceptance Criteria
- All requirements have passing tests
- Existing `MySqlIntrospectorTest` fixture rows either gain the new keys or the introspector reads them null-safely (`?? null`); no undefined-key warnings

## Implementation Notes
- Parse ON UPDATE case-insensitively from anywhere in EXTRA (e.g. `/on update (\S+)/i`); keep the existing `auto_increment` check.
- `COLLATION_NAME` is NULL for non-string columns; keep null.
