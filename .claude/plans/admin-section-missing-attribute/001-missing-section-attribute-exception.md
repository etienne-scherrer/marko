# Task 001: Throw missingSectionAttribute from parseAdminSectionClass

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `AdminException::missingSectionAttribute()` and make `parseAdminSectionClass()` check for `#[AdminSection]` before the interface check, so a class without the attribute gets a helpful exception instead of a PHP warning and `Error`.

## Context
- Related files: packages/admin/src/Discovery/AdminSectionDiscovery.php, packages/admin/src/Exceptions/AdminException.php, packages/admin/tests/Unit/Discovery/AdminSectionDiscoveryTest.php
- Message: "Class '…' is not marked with #[AdminSection]"; context: "While parsing admin section class '…'"; suggestion: "Add #[AdminSection(id: ..., label: ...)] to the class, or don't pass it to admin section discovery"

## Requirements (Test Descriptions)
- [ ] `it throws AdminException naming the class when the class has no AdminSection attribute`
- [ ] `it suggests adding the AdminSection attribute when the attribute is missing`
- [ ] `it throws the missing attribute exception rather than the interface exception when a class has neither`
- [ ] `it still throws the interface exception for a class with the attribute but without the interface`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
Implemented with strict TDD (tests written first and observed failing). See the commit for the code.
