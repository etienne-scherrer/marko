# Task 006: SyncPermissionsCommand report, --prune, --force, event

**Status**: completed
**Depends on**: 001, 003, 004, 005
**Retry count**: 0

## Description
The command syncs, prints created/updated counts, lists unregistered keys with role counts and wildcard grants kept, and hints at `--prune`. With `--prune` (and stale rows present) it runs `DestructiveCommandGuard::check()` with `allowInProduction: true, confirmInDevelopment: true`, then `pruneUnregistered()`, and lists what it removed. It dispatches `PermissionsSynced` with the counts. Flags `prune` and `force` are value-less.

## Context
- Related files: `SyncPermissionsCommand.php`, `tests/Unit/Command/SyncPermissionsCommandTest.php`; use `FakeConfirmationPrompter`, `FakeEventDispatcher`, `FakeClock` from marko/testing

## Requirements (Test Descriptions)
- [x] `it reports unregistered permissions with role counts and deletes nothing without --prune`
- [x] `it prunes in development when nobody can answer`
- [x] `it asks before pruning in development when interactive`
- [x] `it refuses --prune without --force in staging and production`
- [x] `it cancels the prune when the confirmation is declined with --force`
- [x] `it prunes with --force when nobody can answer in production`
- [x] `it lists wildcard grants as kept`
- [x] `it reports updated labels and groups`
- [x] `it dispatches PermissionsSynced with created, updated, unregistered and pruned counts`
- [x] `it skips the guard when --prune finds nothing to remove`
- [x] `it declares prune and force as value-less flags`
- [x] `it still dispatches PermissionsSynced with a zero pruned count when the prune is refused or cancelled`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- New constructor dependencies, all autowired: `DestructiveCommandGuard` (marko/database), `Marko\Core\Event\EventDispatcherInterface` (bound by core Application), and `Psr\Clock\ClockInterface` (bound by marko/clock, which marko/database requires). In tests, build a real guard with `new AppEnvironment(['APP_ENV' => ...])` and `FakeConfirmationPrompter`, as in `DestructiveCommandGuardTest`.
- `#[Command(..., flags: ['prune', 'force'])]`.
- Event: dispatch once, after the sync and any prune attempt. Use `createdCount`, `totalCount` = `registeredCount`, `updatedCount`, `unregisteredCount` (from the sync result), `prunedCount` (rows actually removed, 0 if refused, cancelled or not requested), and `timestamp: $clock->now()`. The sync has already committed when the guard refuses, so the event still fires.
- Exit code: the guard's code when it refuses (1) or cancels (0), otherwise 0.
