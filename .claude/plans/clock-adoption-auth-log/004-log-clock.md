# Task 004: log / log-file — clock for cutoff, record time and rotation

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`log:clear` computes its cutoff from the injected clock; `FileLogger` stamps records with it; `DailyRotation` names files from it (replacing its fixed `?DateTimeImmutable $now` parameter); `FileLoggerFactory` wires the clock through.

## Context
- Related files: packages/log/src/Command/ClearCommand.php, packages/log-file/src/Driver/FileLogger.php, packages/log-file/src/Rotation/DailyRotation.php, packages/log-file/src/Factory/FileLoggerFactory.php

## Requirements (Test Descriptions)
- [ ] `it deletes only files older than the cutoff on the injected clock`
- [ ] `it stamps records with the injected clock`
- [ ] `it names the log file from the injected clock's date`
- [ ] `it rolls over to a new file when the clock crosses midnight`
- [ ] `it builds a logger whose rotation and records follow the injected clock`

## Acceptance Criteria
- All requirements have passing tests
- log and log-file require `marko/clock`
- log-file adds `"marko/testing": "self.version"` to `require-dev` (FakeClock). Today it only has pest. log already has it.
- Existing tests updated: log/tests/Unit/Command/ClearCommandTest.php, log-file/tests/Unit/Driver/FileLoggerTest.php (13 constructions), log-file/tests/Unit/Rotation/DailyRotationTest.php (9 constructions that pass a fixed `DateTimeImmutable` must switch to `FakeClock`)

## Implementation Notes
Exact signatures:
- `ClearCommand::__construct(LogConfig $config, ClockInterface $clock)`. The cutoff is `$clock->now()->getTimestamp() - ...`. In tests, set file mtimes with `touch($file, $fakeNow - N)` relative to the fake clock.
- `DailyRotation::__construct(ClockInterface $clock)`. This replaces `?DateTimeImmutable $now`.
- `FileLogger::__construct(string $path, string $channel, LogLevel $minimumLevel, LogFormatterInterface $formatter, ClockInterface $clock, ?RotationStrategyInterface $rotation = null)`
- `FileLoggerFactory::__construct(LogConfig $config, LogFormatterInterface $formatter, ClockInterface $clock)`. It is autowired by log-file/module.php, so no module change is needed.

`FileLogger` and `DailyRotation` are `readonly class`es. `$rotation` cannot stay promoted with a `??` fallback: declare `private RotationStrategyInterface $rotation;` and assign `$this->rotation = $rotation ?? new DailyRotation($clock);` in the constructor body (readonly allows a single assignment in the constructor).

As built: `FileLoggerFactory` also takes `RotationStrategyInterface $rotation`, and log-file/module.php binds `RotationStrategyInterface` with a closure that resolves `DailyRotation` from the container. That shares the bound clock and makes the documented `#[Preference(replaces: DailyRotation::class)]` take effect (before, the factory built `new DailyRotation()` itself, so the Preference was ignored). Covered by `ModuleWiringTest`.
