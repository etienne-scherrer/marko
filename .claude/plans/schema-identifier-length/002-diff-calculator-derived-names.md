# Task 002: Shorten Derived Index Names in DiffCalculator

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Build the `<table>_<column>_unique` and `<table>_<column>_index` names through `IdentifierName::derive()` so a long table and column never produce a name MySQL rejects or PostgreSQL truncates.

## Context
- Related files: packages/database/src/Diff/DiffCalculator.php (`deriveUniqueColumnIndexes()`, `foreignKeyReplacementIndexes()`), packages/database/tests/Diff/DiffCalculatorTest.php
- Unique indexes are matched by column, not name, so no matching logic changes.
- Use `IdentifierName::derive("{$entityTable->name}_{$column->name}", suffix: '_unique')` and `IdentifierName::derive("{$entityTable->name}_{$columnName}", suffix: '_index')` (contract in task 001).
- A derived unique index only exists for an existing column becoming unique (columns being added are skipped). Unit tests must pass a database table that already has the column as non-unique.
- Update the docblocks of `deriveUniqueColumnIndexes()` and `foreignKeyReplacementIndexes()` to mention shortening.

## Requirements (Test Descriptions)
- [x] `it keeps the derived unique index name users_email_unique unchanged`
- [x] `it shortens a derived unique index name over 63 bytes`
- [x] `it shortens a derived foreign key replacement index name over 63 bytes`
- [x] `it gives two long unique columns sharing a prefix different derived names`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Done.
