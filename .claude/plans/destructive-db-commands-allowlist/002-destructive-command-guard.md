# Task 002: DestructiveCommandGuard

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Create `Marko\Database\Command\DestructiveCommandGuard`, the single place that decides whether a destructive command may run in the current environment, writing a loud message when it may not.

## Context
- Related files: packages/database/src/Command/MigrateCommand.php (`confirmDestructiveChanges()` contract), packages/core/src/Command/ConfirmationPrompterInterface.php
- `check(string $command, string $effect, Input $input, Output $output): ?int` — null to proceed, else exit code
- Production: `Error: db:rebuild cannot be run in the 'production' environment.` + `This command <effect> and is never allowed in production, even with --force.` -> 1
- Development/testing: null, nothing asked
- Other names without `--force`: `Error: db:rebuild is refused in the 'staging' environment without --force.` + explanation that only development and testing run it without `--force` -> 1
- Other names with `--force`, interactive: ask `This command <effect> in the 'staging' environment. Continue?`; declined -> `db:rebuild cancelled.` -> 0
- Other names with `--force`, non-interactive: null
- `$effect` must read correctly in both sentences above ("This command <effect> and ..." / "This command <effect> in the '<env>' environment. Continue?"). Callers pass (fixed, used by 003/005 and quoted in docs):
  - `db:rebuild`: `drops every table and re-runs all migrations`
  - `db:reset`: `rolls back every migration`
  - `db:rollback`: `rolls back migrations`
  - `db:seed`: `writes seed data to the database`
- Confirmation uses `confirm($question, false)` (default no). Use `$input->hasOption('force')`, the same check `MigrateCommand` uses.
- Tests use `new AppEnvironment(['APP_ENV' => ...])` and `Marko\Testing\Fake\FakeConfirmationPrompter` (`interactive: false` for the "nobody can answer" case; `assertNothingAsked()` for dev/testing/production/refused cases)

## Requirements (Test Descriptions)
- [x] `it allows development and testing environments without asking`
- [x] `it refuses production even with --force`
- [x] `it refuses staging and unknown environments without --force, naming the environment and the flag`
- [x] `it allows a non-production environment with --force when nobody can answer`
- [x] `it asks for confirmation with --force when interactive and proceeds on yes`
- [x] `it cancels with exit code 0 when the confirmation is declined`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
