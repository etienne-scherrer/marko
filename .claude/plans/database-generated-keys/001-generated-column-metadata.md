# Task 001: Generated column metadata and parse-time validation

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `generated: bool = false` to the `#[Column]` attribute, carry it into `PropertyMetadata` as `isGenerated`, and make `EntityMetadataFactory::parse()` reject invalid uses with `EntityException`.

## Context
- Related files: `packages/database/src/Attributes/Column.php`, `packages/database/src/Entity/PropertyMetadata.php`, `packages/database/src/Entity/EntityMetadataFactory.php`, `packages/database/src/Exceptions/EntityException.php`, `packages/database/tests/Entity/EntityMetadataFactoryTest.php`
- Patterns to follow: `EntityException::autoIncrementWithoutPrimaryKey()` and its check in the factory
- Add the new constructor parameters at the END of `Column` and `PropertyMetadata` so positional callers don't break.
- "Without a default" means `$columnAttr->default === null` (the PHP property default is not a database generator).

## Requirements (Test Descriptions)
- [x] `it marks a generated primary key as generated in the property metadata`
- [x] `it leaves isGenerated false when the column does not declare generated`
- [x] `it throws when generated is declared on a column that is not the primary key`
- [x] `it throws when generated is combined with autoIncrement`
- [x] `it throws when generated is declared without a default`

## Acceptance Criteria
- All requirements have passing tests
- Exceptions use message/context/suggestion and name entity and property

## Implementation Notes
