# Task 001: marko/clock package with SystemClock

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Scaffold `packages/clock` (namespace `Marko\Clock`) requiring `psr/clock: ^1.0`, with `SystemClock implements Psr\Clock\ClockInterface` and a `module.php` binding `ClockInterface` to `SystemClock` as a singleton. Wire it into the root `composer.json`.

## Context
- Related files: packages/clock/*, composer.json (repositories, require, autoload-dev)
- Patterns to follow: packages/hashing, packages/env

## Requirements (Test Descriptions)
- [x] `it implements the PSR-20 clock interface`
- [x] `it returns the current time`
- [x] `it uses the default timezone when none is given`
- [x] `it returns the time in the given timezone`
- [x] `it accepts a timezone name`
- [x] `it throws on an invalid timezone name`
- [x] `it binds ClockInterface to SystemClock as a singleton`
- [x] `it has no hardcoded version in composer.json`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
`SystemClock::__construct(DateTimeZone|string|null $timezone = null)` — a union type so the container uses the default instead of trying to autowire `DateTimeZone`. A null timezone resolves `date_default_timezone_get()` on each `now()` call.
