# Task 011: App integration suite: flip #176 todos

**Status**: pending
**Depends on**: [002, 006, 007]
**Retry count**: 0

## Description
Replace the two #176 todos in KnownGapsTest with real tests that run against the booted fixture app on Postgres.

## Context
- Related files: tests/Integration/App/KnownGapsTest.php, tests/Integration/App/ServicesTest.php

## Requirements (Test Descriptions)
- [ ] `it rolls back a savepoint without rolling back the outer transaction`
- [ ] `it runs an after-commit callback only when the transaction commits`
- [ ] `it locks a row through Repository::query()->lockForUpdate() inside TransactionInterface::transaction()` (proves the query builder factory's connection is the same instance as TransactionInterface in the booted app; place it in ServicesTest, not KnownGapsTest)

Notes: resolve `TransactionInterface` from the booted container. Remove the two #176 entries and keep HarnessTest's ticket list (`tests/Integration/App/HarnessTest.php:72`) consistent. Drop 176 from it if that test asserts every listed ticket still has a todo.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
