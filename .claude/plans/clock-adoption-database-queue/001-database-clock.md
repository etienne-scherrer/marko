# Task 001: database: Repository and MigrationGenerator read the clock

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`Repository::now()` and `MigrationGenerator` read time from an injected `ClockInterface`, so `#[Timestamps]` values and migration file names are deterministic under `FakeClock`.

## Context
- Related files: packages/database/src/Repository/Repository.php, packages/database/src/Migration/MigrationGenerator.php, packages/database/composer.json
- Repository gets optional trailing `ClockInterface $clock = new SystemClock()` (implemented as a promoted parameter with a `new` default); MigrationGenerator gets a required clock.

## Requirements (Test Descriptions)
- [ ] `it stamps created_at and updated_at from the injected clock in UTC` (FakeClock at a non-UTC instant, e.g. `2026-10-05 14:00:00+02:00`; assert `12:00:00` UTC, so a missing conversion fails)
- [ ] `it stamps updated_at from the injected clock when updating`
- [ ] `it falls back to the system clock when no clock is given`
- [ ] `it names migration files from the injected clock`
- [ ] `it increments migration file timestamps by one second per file from the injected clock`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- `Repository::now()` stays `protected` and overridable (documented in database.md)
- No `date(` / `time()` left in MigrationGenerator

## Implementation Notes
- Repository: take the param non-promoted and resolve it once in the constructor into a private property (`$this->clock = $clock ?? new SystemClock()`). Then `now()` returns `$this->clock->now()->setTimezone(new DateTimeZone('UTC'))`.
- MigrationGenerator: replace `date('YmdHis', time() + $this->timestampOffset)` with `$this->clock->now()->modify("+{$this->timestampOffset} seconds")->format('YmdHis')`. Add the clock as the last required param (no defaults exist). Update call sites: `tests/Migration/Helpers.php:279`, `tests/Migration/MigrationGeneratorTest.php:105`, `tests/Feature/EntityToMigrationWorkflowTest.php:191`.
- Add `marko/clock` to `require` (not dev) since Repository instantiates `SystemClock`.
