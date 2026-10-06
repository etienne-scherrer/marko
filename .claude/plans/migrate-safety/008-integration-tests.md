# Task 008: Integration tests for #170

**Status**: completed
**Depends on**: 003, 004, 006, 007
**Retry count**: 0

## Description
Flip the two #170 todos in tests/Integration/App/KnownGapsTest.php into real tests against Postgres, and update the `--no-generate` harness note that names #170.

## Context
- Related files: tests/Integration/App/KnownGapsTest.php, tests/Integration/App/Helpers.php, tests/Integration/App/Fixture/

## Requirements (Test Descriptions)
- [x] `it keeps a hand-made partial index when db:migrate runs`
- [x] `it never generates migrations when db:migrate runs in production`

## Acceptance Criteria
- Tests pass against the compose services

## Implementation Notes
- Both flipped tests must keep `->issue(170)`: HarnessTest greps `*Test.php` for `issue(: |\()170` and fails otherwise.
- Partial-index test: an undeclared index is dropped by design, so the fixture must opt it out. Add the index name to
  the fixture's `config/database.php` `migrations.ignore_indexes` (exercises the module.php DiffCalculator binding end
  to end); create the index with a committed fixture migration; run `db:migrate` in development without
  `--no-generate`; assert exit 0 and that no generated migration file contains a DROP of that index and the index
  still exists in `pg_indexes`. If any destructive change appears, the command exits 1 non-interactively, which the
  test should surface as a failure. Do not pass `--force`; it would hide the bug.
- Environment: `AppEnvironment` reads `MARKO_ENV` before `APP_ENV`, lazily, from `$_ENV` then `getenv()`. Set
  `APP_ENV` via both `$_ENV` and `putenv()`, unset `MARKO_ENV`, and restore all of them in `finally`. Unset = production,
  which is the suite's current default.
- Production test: add an entity change (or rely on fixture drift) so a diff exists; assert the migrations directory
  file list is unchanged after `db:migrate`, exit 0, and output contains the drift warning.
- Search Fixture/ for `170` and remove workarounds; update the `--no-generate` comment in Helpers.php (the harness may
  keep the flag, but the comment must no longer cite #170 as an open gap).
