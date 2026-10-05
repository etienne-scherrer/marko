# Plan: CLI Long Option Values

## Created
2026-10-05

## Status
completed

## Objective
Make `Marko\Core\Command\Input` parse argv once into positionals and options so `--name value` works like `--name=value`, positionals never contain option tokens, and commands can declare value-less flags via `#[Command(flags: [...])]`.

## Related Issues
Closes #184

## Discovery Notes
- `Input` (packages/core/src/Command/Input.php) parses on demand; long options only accept `--name=value`; `getArguments()` returns every token after the command name, options included.
- `#[Command]` has `name`, `description`, `aliases`. `CommandDiscovery` builds `CommandDefinition`; `DiscoveryCache` serializes definitions with a `CACHE_VERSION` (mismatch is a loud error with a "run discovery:clear" suggestion).
- `Input` is constructed in `CliKernel` and `MarkoConsoleDispatcher` (mcp); both execute through `CommandRunner::run()`, so the runner is the single place to apply declared flags.
- Many commands hand-parse `getArguments()` for options (`--force`, `--all`, `--once`, `--queue=`, `--sleep=`, `--class=`, `--step=`, `--days=`, `--subject=`, `--no-generate`, `--verbose`/`-v`). Once `getArguments()` returns positionals only, every one of these must migrate to `hasOption()` / `getOption()` or it silently breaks. This is a necessary extension of the ticket's audit.
- `page-cache:purge` reads the tag from `getArgument(0)` and treats `--tag` as a flag; its error message says `--tag <tag>`.

## Scope

### In Scope
- `Input` parse-once into `positionals` + `options` (keyed by `-x` / `--name` to preserve the existing single-char = short, multi-char = long semantics); `--` terminator; repeated options; `getOptionValues()`; `withFlags()`.
- `flags` on `#[Command]`, `CommandDefinition`, `CommandDiscovery`, `DiscoveryCache` (version bump), applied by `CommandRunner`.
- Migrate every shipped command that parses options by hand; declare all boolean flags.
- Fix `page-cache:purge --tag homepage`.
- Docs: `cli.md`, `core.md` (+ any affected package docs pages).

### Out of Scope
- Full signature DSL, combined short flags (`-abc`), option validation / unknown-option errors.

## Success Criteria
- [x] Every exit criterion in #184 has a test
- [x] All shipped commands with boolean flags declare them
- [x] All tests passing, `composer ci` green
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Parse-once Input with long option values, `--`, repeats, declared flags | - | completed |
| 002 | `flags` on #[Command], definition, discovery, cache, runner | 001 | completed |
| 003 | Queue + page-cache commands (work, retry, purge) | 001, 002 | completed |
| 004 | Database, log, mail, authentication commands | 001, 002 | completed |
| 005 | Devai + devserver flag declarations | 001, 002 | completed |
| 006 | Docs: cli.md, core.md, affected package pages | 001-005 | completed |

## Architecture Notes
- `Input` stays a `readonly class`; parsed state is set in the constructor. `withFlags(list<string>)` returns a new `Input` re-parsed with the declarations (the runner calls it).
- Short options keep their current behavior: `-p 8000`, `-p=8000`, `-d`.
- Undeclared bare long option followed by a non-dash token consumes it (documented heuristic, mirrors short options).

## Risks & Mitigations
- Behavior change in `getArguments()`: audited every caller in packages/*/src; noted in PR body as a changelog item.
- Stale discovery caches lacking `flags`: bump `CACHE_VERSION` so old caches fail loudly with the existing "run discovery:clear" suggestion instead of silently dropping flags.
