# Task 002: admin-auth — events take the repository's timestamp

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
The seven admin-auth events stop calling `new DateTimeImmutable()` in a default parameter. The repositories that dispatch them pass the instant from `Repository::now()`, the database package's time seam (converted to `ClockInterface` by the database group of #221).

## Context
- Related files: packages/admin-auth/src/Events/*.php, packages/admin-auth/src/Repository/RoleRepository.php, packages/admin-auth/src/Repository/AdminUserRepository.php
- Patterns to follow: issue #221 step 3 (dispatchers pass the time)

## Requirements (Test Descriptions)
- [ ] `it requires a timestamp on every admin-auth event`
- [ ] `it stamps RoleCreated with the repository's current instant`
- [ ] `it stamps RoleUpdated with the repository's current instant`
- [ ] `it stamps RoleDeleted with the repository's current instant`
- [ ] `it stamps AdminUserCreated and AdminUserUpdated with the repository's current instant`

## Acceptance Criteria
- All requirements have passing tests
- No wall-clock reads in packages/admin-auth/src/Events
- `packages/admin-auth/tests/Unit/Config/AdminAuthConfigTest.php` (lines ~110-147 construct all seven events, including `AdminUserDeleted` and `PermissionsSynced`, without a timestamp) updated to pass one

## Implementation Notes
No constructor change to the repositories, to avoid colliding with the database group's change to the `Repository` constructor.

- Keep the parameter name `$timestamp` and its position (last) in each event; only drop the default.
- `AdminUserDeleted` and `PermissionsSynced` are never dispatched in `src/`. Change only their signature and do not add new dispatches.
- Test seam: build the repository as an anonymous subclass that overrides `protected function now(): DateTimeImmutable` to return a fixed instant, then assert the dispatched event's timestamp equals it. Do not depend on `ClockInterface` reaching `Repository` (that is the database group's change).
- `Repository::now()` returns UTC, while the old default used PHP's default timezone. Event timestamps are now UTC. Call this out in the admin-auth docs (task 006).
