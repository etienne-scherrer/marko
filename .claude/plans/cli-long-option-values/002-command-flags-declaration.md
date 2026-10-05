# Task 002: Declare flags on #[Command] and apply them in CommandRunner

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Add `flags` to the `#[Command]` attribute and `CommandDefinition`, carry it through `CommandDiscovery` and `DiscoveryCache`, and have `CommandRunner` pass it into `Input`.

## Context
- Related files: packages/core/src/Attributes/Command.php, Command/CommandDefinition.php, Command/CommandDiscovery.php, Discovery/DiscoveryCache.php, Command/CommandRunner.php

## Requirements (Test Descriptions)
- [x] `it defaults command flags to an empty list`
- [x] `it discovers declared flags from the Command attribute`
- [x] `it round-trips command flags through the discovery cache`
- [x] `it passes declared flags into the input given to the command`

## Acceptance Criteria
- All requirements have passing tests
- Cache version bumped so stale caches fail loudly instead of silently dropping flags

## Implementation Notes
(Left blank - filled in by programmer during implementation)
