# Task 006: db:migrate environment gating, --generate, drift listing

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`db:migrate` takes `AppEnvironment`. It auto-generates only in development; `--generate` forces it anywhere and `--no-generate` suppresses it. When it does not generate because of the environment, it prints a drift warning listing the differing SQL.

## Context
- Related files: packages/database/src/Command/MigrateCommand.php, tests/Command/MigrateCommandTest.php

## Requirements (Test Descriptions)
- [x] `it applies pending migrations but does not generate in production`
- [x] `it lists the differing statements in the production drift warning`
- [x] `it does not generate when APP_ENV is staging`
- [x] `it generates in local`
- [x] `it does not generate in local with --no-generate`
- [x] `it generates outside development with --generate`
- [x] `it rejects --generate combined with --no-generate`
- [x] `it prints the drift warning in production even after applying pending migrations`
- [x] `it declares generate as a value-less flag`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Replace `bool $isProduction = false` with required `AppEnvironment $appEnvironment`; gate generation on
  `isDevelopment()` (staging and unset do not generate).
- Add `generate` to `#[Command(flags: [...])]` alongside `no-generate`, `verbose`, `v`.
- Today the drift warning only runs inside `if ($schemaCount === 0 && $dataCount === 0)`. Move the drift check so it
  runs after pending files are applied whenever generation was skipped because of the environment, listing each
  statement from `$this->sqlGenerator->generateUp($diff)`. Keep the exit code 0.
- Update existing MigrateCommandTest call sites: the helper (`bool $isProduction = false` at ~line 302) and the direct
  `new MigrateCommand(...)` calls (~689, ~735) must pass `appEnvironment:`. Tests that expect generation must pass a
  development environment explicitly (`new AppEnvironment(['APP_ENV' => 'local'])`); empty variables = production.
