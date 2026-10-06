# Task 003: PermissionDiscovery stops swallowing exceptions

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`PermissionDiscovery::discoverFromClass()` catches `AdminException|ReflectionException` and does nothing. Remove the catch so a bad class surfaces loudly, and declare the exceptions with `@throws`.

## Context
- Related files: packages/admin-auth/src/Discovery/PermissionDiscovery.php, packages/admin-auth/tests/Unit/Discovery/PermissionDiscoveryTest.php
- `AdminSectionDiscovery::parseAdminSectionClass()` throws `AdminException::sectionMustImplementInterface()` for non-section classes

## Requirements (Test Descriptions)
- [x] `it throws AdminException when the class does not implement AdminSectionInterface`
- [x] `it throws ReflectionException when the class does not exist`
- [x] `it registers nothing when discovery fails`

## Gotchas
- Add `@throws AdminException|ReflectionException` to `discoverFromClass()` docblock. Remove the now-unused imports only if they are no longer referenced (they still are, via `@throws`).
- `$className` is typed `class-string`. For the "class does not exist" test, pass the value through a plain `string` variable so PHPStan does not flag it, or add a targeted `@phpstan-ignore`. `composer phpstan` must stay at zero errors.
- The "not an AdminSectionInterface" fixture needs a class that does not implement the interface. Add it at the bottom of the existing test file with a unique name.

## Acceptance Criteria
- All requirements have passing tests
- Existing discovery tests still pass
- `composer phpstan` reports zero errors

## Implementation Notes
Implemented as specified.
