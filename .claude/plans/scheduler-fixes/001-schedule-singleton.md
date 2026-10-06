# Task 001: Schedule Singleton + Boot Registration Regression Test

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Mark `Schedule` as a singleton in `packages/scheduler/module.php` so the instance filled by a module `boot` callback is the same one injected into the scheduler commands. Prove it with a container-level test that loads the real module manifest.

## Context
- Related files: `packages/scheduler/module.php`, `packages/core/src/Container/BindingRegistry.php`, `packages/core/src/Module/ManifestParser.php`
- Patterns to follow: list-style `singletons` in other module.php files

## Requirements (Test Descriptions)
- [x] `it declares Schedule as a singleton in module.php`
- [x] `it resolves the same Schedule instance from the container on every request`
- [x] `it executes a task registered through a module boot callback when schedule:run runs`

## Acceptance Criteria
- All requirements have passing tests
- Test uses Container + BindingRegistry + the parsed scheduler manifest, not a hand-built `Schedule`

## Implementation Notes
(Left blank - filled in by programmer during implementation)
