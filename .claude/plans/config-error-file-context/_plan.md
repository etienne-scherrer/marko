# Plan: Config Error File Context

## Created
2026-10-06

## Status
completed

## Objective
Make a Marko exception thrown while a config file runs (an `Env` rejection, a `DiscoveryCacheException`) name the config file that raised it, keeping its context, suggestion and the original as `getPrevious()`.

## Related Issues
Closes #343

## Discovery Notes
- `ConfigLoader::load()` only catches `ParseError` and wraps it in `ConfigLoadException` (which appends `[file: ...]` to its message and hardcodes the suggestion).
- `ConfigException` and `DiscoveryCacheException` both extend `Marko\Core\Exceptions\MarkoException`, which carries `getContext()`/`getSuggestion()`.
- `ErrorReport` (marko/errors) takes message, context and suggestion from the top-level exception; errors-simple/errors-advanced formatters also render `previous`; `CliKernel` prints `getMessage()` and `getSuggestion()`. Putting the file in the wrapper's message is therefore enough for every renderer to show it.
- `ConfigDiscovery` loads every module's `config/*.php` through `ConfigLoader::load()`, so the wrap covers boot.
- Ask was concrete (issue lists approach and exit criteria); no clarification round.

## Scope

### In Scope
- `ConfigLoadException::fromFileFailure(string $filePath, MarkoException $previous)` named constructor
- `ConfigLoader::load()` catches `MarkoException` from the required file and rethrows via `fromFileFailure()`
- Fixtures + tests for `Env::int()`, `Env::bool()`, `DiscoveryCacheException` (core `config/discovery.php`), and a non-Marko error
- `config.md` docs updates (Environment Variables error example, ConfigLoader API section)

### Out of Scope
- Wrapping non-Marko throwables (`TypeError`, `Error`) — they pass through unchanged, per the issue's recommendation
- Changing `Env` itself or renderer packages

## Success Criteria
- [ ] Env rejection during load produces a `ConfigLoadException` naming the file, with original context, suggestion and previous
- [ ] Same for `DiscoveryCacheException` from `core/config/discovery.php`
- [ ] `Env` outside the loader unchanged; non-Marko errors unchanged
- [ ] Docs updated
- [ ] All tests passing
- [ ] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Wrap Marko exceptions from config files with the file path | - | completed |
| 002 | Document file-annotated config load errors | 001 | completed |

## Architecture Notes
- Keep `ConfigLoader` dependency-free (callable before the container exists).
- The wrapper must stay a `ConfigException` subclass so `catch (ConfigException)` still matches.
- If the thrown exception is already a `ConfigLoadException` (nested load), it is still wrapped — the outer file is the one that ran; not a real case today, so no special handling.

## Risks & Mitigations
- Fixtures that read env vars leak state between tests: set/unset `$_ENV` and `putenv` within each test with try/finally.
