# Task 005: Guard db:seed

**Status**: completed
**Depends on**: 001, 002, 003, 004
**Retry count**: 0

## Description
Replace `SeedCommand`'s inline production check with `DestructiveCommandGuard`, declare `force` as a flag, and forward `--force` to `SeederRunner`.

## Context
- Related files: packages/database/src/Command/SeedCommand.php, packages/database/tests/Command/SeedCommandTest.php, tests/Integration/App/MigrationSafetyTest.php
- Inject `DestructiveCommandGuard $destructiveCommandGuard` in place of `AppEnvironment`; `#[Command(..., flags: ['force'])]` (`class` stays a value option)
- Effect passed to `check()`: `writes seed data to the database`
- Run the guard first, before seeder discovery, as the current production check does
- Forward `force: $input->hasOption('force')` to BOTH `runAll()` and `runByName()` (`--class`); otherwise a staging `--force --class x` run passes the guard and then fails in the runner with `requiresForce`
- `createSeedCommand()` in SeedCommandTest passes `appEnvironment:` to `SeedCommand`. Switch it to a guard built from the same `AppEnvironment` the `SeederRunner` gets, plus a `FakeConfirmationPrompter` parameter
- Integration: MigrationSafetyTest (`refuses db:rebuild and db:seed in production and when APP_ENV is unset`, group `integration-services`) asserts `toContain('Rebuild cannot be run in production')` and `toContain('Seeders cannot be run in production')`. Update both to the guard text (`db:rebuild cannot be run in the 'production' environment`, `db:seed cannot be run in the 'production' environment`); this task owns the whole file. Never pass `--force` in a non-dev integration case without `--no-interaction` (a TTY prompt would hang the suite)

## Requirements (Test Descriptions)
- [x] `it seeds in development and testing`
- [x] `it refuses staging and an unknown environment without --force, naming the environment and the flag`
- [x] `it seeds in staging with --force when nobody can answer`
- [x] `it asks for confirmation with --force when interactive`
- [x] `it refuses production even with --force`
- [x] `it declares force as a value-less flag`
- [x] `it forwards --force to the runner for a single --class seeder in staging`
- [x] `it does not discover seeders when refused`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
