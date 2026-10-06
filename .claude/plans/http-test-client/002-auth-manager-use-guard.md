# Task 002: AuthManager::useGuard()

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add a public `useGuard(string $name, GuardInterface $guard): void` to `AuthManager` so tests (and apps) can put a guard instance in place for a guard name.

## Context
- Related files: packages/authentication/src/AuthManager.php, packages/authentication/tests

## Requirements (Test Descriptions)
- [x] `it returns the guard registered with useGuard for that name`
- [x] `it uses the registered guard for the default guard when no name is given`
- [x] `it replaces a guard that was already built`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
