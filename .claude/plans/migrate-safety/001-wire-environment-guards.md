# Task 001: Wire AppEnvironment into rebuild/reset/rollback/seed guards

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Replace the never-set `bool $isProduction = false` constructor parameter with core `AppEnvironment` in `RebuildCommand`, `ResetCommand`, `RollbackCommand`, `SeedCommand` and `SeederRunner`, so the production guards actually fire. Unset APP_ENV is production (fails safe).

## Context
- Related files: packages/database/src/Command/{Rebuild,Reset,Rollback,Seed}Command.php, packages/database/src/Seed/SeederRunner.php, packages/database/module.php
- Patterns to follow: `Marko\Core\Environment\AppEnvironment` with an explicit `variables` array in tests

## Requirements (Test Descriptions)
- [x] `it refuses to rebuild and exits 1 when APP_ENV is production`
- [x] `it refuses to rebuild and exits 1 when APP_ENV is unset`
- [x] `it refuses to seed and exits 1 when APP_ENV is production`
- [x] `it refuses to seed and exits 1 when APP_ENV is unset`
- [x] `it refuses to reset and to rollback in production`
- [x] `it blocks SeederRunner in production`
- [x] `it builds SeederRunner from module.php with the container AppEnvironment`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
- `SeederRunner` signature: `array $seeders, AppEnvironment $appEnvironment, ?TransactionInterface $transaction = null`
  (required, no default). The module.php closure passes `$container->get(AppEnvironment::class)`.
- Commands take `private AppEnvironment $appEnvironment` (required, no default) and call `isProduction()`.
- Update every existing call site that passes `isProduction:` to pass `appEnvironment: new AppEnvironment(['APP_ENV' => ...])`:
  tests/Command/RollbackCommandTest.php, ResetCommandTest (if present), RebuildCommandTest.php, SeedCommandTest.php
  (helper at ~line 76), tests/Seed/SeederRunnerTest.php, tests/Feature/SeederExecutionTest.php. Non-production cases
  must pass a development env explicitly (an empty variables array means production).
- Do NOT touch MigrateCommand here (task 006 owns it).
- module.php edits: keep them additive and minimal (003 and 007 also edit the bindings array).
