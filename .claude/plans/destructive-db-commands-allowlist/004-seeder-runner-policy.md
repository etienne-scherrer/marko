# Task 004: SeederRunner force policy

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Make `SeederRunner`'s own guard follow the same policy: production always throws `blockedInProduction()`, development/testing run, any other environment throws `SeederException::requiresForce($environment)` unless `force: true` is passed to `runAll()`/`runByName()`.

## Context
- Related files: packages/database/src/Seed/SeederRunner.php, packages/database/src/Exceptions/SeederException.php, packages/database/tests/Seed/SeederRunnerTest.php, packages/database/tests/Feature/SeederExecutionTest.php
- Signatures: `runAll(array $definitions, bool $force = false): void`, `runByName(string $name, array $definitions, bool $force = false): void`. Constructor unchanged (module.php factory and ModuleBindingsTest unaffected). Update `@throws` docblocks
- `SeederException::requiresForce(string $environment)`: message names the environment (e.g. `Seeders cannot be run in the 'staging' environment without force`), suggestion mentions `--force` on `db:seed` / `force: true` for programmatic callers
- Update `blockedInProduction()`'s suggestion: it currently says "Set MARKO_ENV or APP_ENV to development/local". It should also mention testing
- The environment check stays before the seeder lookup in `runByName()`, so `seederNotFound` is not thrown ahead of the policy error

## Requirements (Test Descriptions)
- [x] `it runs seeders in development and testing without force`
- [x] `it throws requiresForce naming the environment in staging without force`
- [x] `it runs seeders in staging with force`
- [x] `it throws blockedInProduction in production even with force`
- [x] `it applies the same policy to runByName`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
