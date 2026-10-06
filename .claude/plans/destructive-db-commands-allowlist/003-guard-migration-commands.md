# Task 003: Guard db:rebuild, db:reset, db:rollback

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Replace the inline `isProduction()` checks in `RebuildCommand`, `ResetCommand` and `RollbackCommand` with `DestructiveCommandGuard`, and declare `force` as a value-less flag on each.

## Context
- Related files: packages/database/src/Command/{Rebuild,Reset,Rollback}Command.php and their tests in packages/database/tests/Command/
- Inject `DestructiveCommandGuard $destructiveCommandGuard` in place of `AppEnvironment`
- Declare flags: `#[Command(name: ..., description: ..., flags: ['force'])]` (rollback keeps `--step` as a value option, so `step` must NOT be in `flags`)
- Effects passed to `check()` (from task 002): rebuild `drops every table and re-runs all migrations`, reset `rolls back every migration`, rollback `rolls back migrations`
- Existing tests construct commands with `appEnvironment:` (RollbackCommandTest ~13 times, RebuildCommandTest helper, ResetCommandTest) and assert the old messages (`Rebuild cannot be run in production environment.`, `Rollback is never allowed in production, even with --force.` etc.). Switch every construction to `destructiveCommandGuard: new DestructiveCommandGuard(new AppEnvironment([...]), new FakeConfirmationPrompter(...))` and update the old-message assertions to the guard text
- Integration: tests/Integration/App/MigrationSafetyTest.php asserts the old `db:rebuild` message in the same test case as `db:seed`. Task 005 (which depends on this task) owns that file, so do NOT edit it here, to avoid parallel conflicts

## Requirements (Test Descriptions)
For each of the three commands:
- [x] `it runs in development and testing`
- [x] `it refuses staging and an unknown environment without --force, naming the environment and the flag`
- [x] `it runs in staging with --force when nobody can answer`
- [x] `it asks for confirmation with --force when interactive`
- [x] `it refuses production even with --force`
- [x] `it declares force as a value-less flag`

## Acceptance Criteria
- All requirements have passing tests
- Migrator is never called when refused or declined
- No remaining assertions of the old production messages in packages/database/tests

## Implementation Notes
