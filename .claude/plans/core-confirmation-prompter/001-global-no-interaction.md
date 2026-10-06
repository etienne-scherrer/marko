# Task 001: Global --no-interaction and per-run Input/Output in CommandRunner

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Make `--no-interaction` a console-wide option handled once in core, and make the running command's `Input` and `Output` resolvable from the container so console services (the prompter) can use them.

## Context
- Related files: packages/core/src/Command/Input.php, packages/core/src/Command/CommandRunner.php, packages/core/tests/Unit/Command/*
- Patterns to follow: existing Input / CommandRunner tests
- Required order in `CommandRunner::run()` (today it resolves the command BEFORE `withFlags()`, and only calls `withFlags()` when the definition has flags; both must change):
  1. `$input = $input->withFlags(array_values(array_unique([...$definition->flags, 'no-interaction'])))`, always
  2. `$this->container->instance(Input::class, $input)` and `instance(Output::class, $output)`
  3. resolve the command from the container
  4. `$command->execute($input, $output)` with that same `$input` instance
- `Input::isInteractive()` only reflects the flag (`!hasOption('no-interaction')`). The TTY check belongs to the prompter (task 002).

## Requirements (Test Descriptions)
- [ ] `it reports interactive when --no-interaction is not passed`
- [ ] `it reports non-interactive when --no-interaction is passed`
- [ ] `it declares no-interaction as a flag for every command so it never consumes the next argument`
- [ ] `it registers the running command input and output in the container before resolving the command`
- [ ] `it passes the same flag-parsed input instance it registered to the command`
- [ ] `it merges no-interaction with a command's declared flags without duplicating it`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
