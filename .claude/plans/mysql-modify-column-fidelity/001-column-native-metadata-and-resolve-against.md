# Task 001: Column native metadata and resolveAgainst()

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add optional driver-native metadata to `Column` and move the up-migration tolerance rule (formerly `PgSqlGenerator::targetColumn()`) into `Column::resolveAgainst()` so both generators share it.

## Context
- Related files: packages/database/src/Schema/Column.php, packages/database/tests/Schema/ColumnTest.php (or the existing Column test file)
- Patterns to follow: `Column::equals()` tolerances; PHP 8.5 `clone()` with properties for `with*()`

## Requirements (Test Descriptions)
- [x] `it defaults nativeType, collation and onUpdateExpression to null`
- [x] `it ignores native metadata when comparing columns`
- [x] `it keeps native metadata through the with methods`
- [x] `it keeps the previous length and default when the column declares none`
- [x] `it keeps the declared length and default over the previous ones`
- [x] `it keeps the previous nullability for an auto-increment primary key`
- [x] `it carries the previous native type, collation and on-update expression`

## Acceptance Criteria
- All requirements have passing tests
- `equals()` semantics unchanged

## Implementation Notes
